import { contextBridge } from 'electron'
import { electronAPI } from '@electron-toolkit/preload'

import { ipcRenderer } from 'electron'

// Custom APIs for renderer
const api = {
  getPrinters: () => ipcRenderer.invoke('get-printers'),
  printTicket: (htmlContent: string, deviceName: string) => ipcRenderer.invoke('print-ticket', htmlContent, deviceName),
  printTicketTcp: (ip: string, port: number, textLines: string[], paperSize: string) => ipcRenderer.invoke('print-ticket-tcp', ip, port, textLines, paperSize)
}

// Use `contextBridge` APIs to expose Electron APIs to
// renderer only if context isolation is enabled, otherwise
// just add to the DOM global.
if (process.contextIsolated) {
  try {
    contextBridge.exposeInMainWorld('electron', electronAPI)
    contextBridge.exposeInMainWorld('api', api)
  } catch (error) {
    console.error(error)
  }
} else {
  // @ts-ignore (define in dts)
  window.electron = electronAPI
  // @ts-ignore (define in dts)
  window.api = api
}
