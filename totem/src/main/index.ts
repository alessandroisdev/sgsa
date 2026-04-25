import { app, shell, BrowserWindow, ipcMain } from 'electron'
import * as net from 'net'
import { join } from 'path'
import { electronApp, optimizer, is } from '@electron-toolkit/utils'
import icon from '../../resources/icon.png?asset'

function createWindow(): void {
  // Create the browser window.
  const mainWindow = new BrowserWindow({
    width: 900,
    height: 670,
    show: false,
    autoHideMenuBar: true,
    ...(process.platform === 'linux' ? { icon } : {}),
    webPreferences: {
      preload: join(__dirname, '../preload/index.js'),
      sandbox: false
    }
  })

  mainWindow.on('ready-to-show', () => {
    mainWindow.show()
  })

  mainWindow.webContents.setWindowOpenHandler((details) => {
    shell.openExternal(details.url)
    return { action: 'deny' }
  })

  // HMR for renderer base on electron-vite cli.
  // Load the remote URL for development or the local html file for production.
  if (is.dev && process.env['ELECTRON_RENDERER_URL']) {
    mainWindow.loadURL(process.env['ELECTRON_RENDERER_URL'])
  } else {
    mainWindow.loadFile(join(__dirname, '../renderer/index.html'))
  }
}

// This method will be called when Electron has finished
// initialization and is ready to create browser windows.
// Some APIs can only be used after this event occurs.
app.whenReady().then(() => {
  // Set app user model id for windows
  electronApp.setAppUserModelId('com.electron')

  // Default open or close DevTools by F12 in development
  // and ignore CommandOrControl + R in production.
  // see https://github.com/alex8088/electron-toolkit/tree/master/packages/utils
  app.on('browser-window-created', (_, window) => {
    optimizer.watchWindowShortcuts(window)
  })

  // Printer IPC handlers
  ipcMain.handle('get-printers', async (event) => {
    return await event.sender.getPrintersAsync()
  })

  ipcMain.handle('print-ticket', async (event, htmlContent: string, deviceName: string) => {
    // Create a hidden window for printing
    const printWindow = new BrowserWindow({
      show: false,
      webPreferences: {
        nodeIntegration: false,
        contextIsolation: true
      }
    })

    // Load the HTML content directly using data URI
    const encodedHtml = encodeURIComponent(htmlContent)
    await printWindow.loadURL(`data:text/html;charset=utf-8,${encodedHtml}`)

    return new Promise((resolve) => {
      printWindow.webContents.print(
        {
          silent: true,
          deviceName: deviceName,
          color: false,
          margins: { marginType: 'none' }
        },
        (success, failureReason) => {
          printWindow.destroy()
          if (!success) {
            console.error('Print failed:', failureReason)
            resolve({ success: false, reason: failureReason })
          } else {
            resolve({ success: true })
          }
        }
      )
    })
  })

  ipcMain.handle('print-ticket-tcp', async (event, ip: string, port: number, textLines: string[]) => {
    return new Promise((resolve) => {
      const client = new net.Socket()
      
      client.setTimeout(3000)

      client.on('error', (err) => {
        console.error('TCP Print Error:', err)
        resolve({ success: false, reason: err.message })
        client.destroy()
      })

      client.on('timeout', () => {
        resolve({ success: false, reason: 'Connection Timeout' })
        client.destroy()
      })

      client.connect(port, ip, () => {
        // ESC/POS Commands
        const INIT = Buffer.from([0x1B, 0x40]) // Initialize printer
        const ALIGN_CENTER = Buffer.from([0x1B, 0x61, 0x01])
        const ALIGN_LEFT = Buffer.from([0x1B, 0x61, 0x00])
        const TEXT_NORMAL = Buffer.from([0x1D, 0x21, 0x00])
        const TEXT_LARGE = Buffer.from([0x1D, 0x21, 0x11]) // Double width & height
        const CUT = Buffer.from([0x1D, 0x56, 0x00]) // Full cut

        // Convert strings to Buffer (ASCII/Latin1)
        const toBuf = (str: string) => Buffer.from(str + '\n', 'latin1')

        // Build payload
        let payload = Buffer.concat([INIT, ALIGN_CENTER])
        
        // Custom formatting based on our ticket structure
        // Line 0: Header (SGSA)
        // Line 1: Service Name
        // Line 2: Ticket Number (Large)
        // Line 3: Priority
        // Line 4: Date
        
        if (textLines.length > 0) payload = Buffer.concat([payload, TEXT_NORMAL, toBuf(textLines[0])])
        if (textLines.length > 1) payload = Buffer.concat([payload, toBuf(textLines[1])])
        if (textLines.length > 2) payload = Buffer.concat([payload, TEXT_LARGE, Buffer.from('\n', 'latin1'), toBuf(textLines[2]), Buffer.from('\n', 'latin1')])
        if (textLines.length > 3) payload = Buffer.concat([payload, TEXT_NORMAL, toBuf(textLines[3])])
        if (textLines.length > 4) payload = Buffer.concat([payload, toBuf(textLines[4])])

        // Add padding lines and cut
        payload = Buffer.concat([payload, Buffer.from('\n\n\n\n', 'latin1'), CUT])

        client.write(payload, () => {
          client.end()
          resolve({ success: true })
        })
      })
    })
  })

  createWindow()

  app.on('activate', function () {
    // On macOS it's common to re-create a window in the app when the
    // dock icon is clicked and there are no other windows open.
    if (BrowserWindow.getAllWindows().length === 0) createWindow()
  })
})

// Quit when all windows are closed, except on macOS. There, it's common
// for applications and their menu bar to stay active until the user quits
// explicitly with Cmd + Q.
app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit()
  }
})

// In this file you can include the rest of your app's specific main process
// code. You can also put them in separate files and require them here.
