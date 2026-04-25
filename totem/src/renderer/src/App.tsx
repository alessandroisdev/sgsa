import { useEffect, useState, useRef } from 'react'
import { fetchConfig, generateTicket } from './services/api'
import { SettingsScreen } from './components/SettingsScreen'

interface Priority {
  id: string
  name: string
  weight: number
}

interface Service {
  id: string
  name: string
}

type Step = 'loading' | 'error' | 'priority' | 'service' | 'printing'

const generatePrintHtml = (ticket: any) => {
  return `
    <html>
      <head>
        <style>
          body { font-family: monospace; text-align: center; margin: 0; padding: 10px; width: 100%; box-sizing: border-box; }
          h1 { font-size: 24px; margin-bottom: 5px; text-transform: uppercase; }
          .number { font-size: 60px; font-weight: bold; margin: 10px 0; border-top: 2px dashed #000; border-bottom: 2px dashed #000; padding: 15px 0; }
          .service { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
          .priority { font-size: 18px; text-transform: uppercase; }
          .date { font-size: 14px; margin-top: 20px; color: #333; }
        </style>
      </head>
      <body>
        <h1>Emissão de Senha</h1>
        <div class="service">${ticket.service?.name}</div>
        <div class="number">${ticket.formatted_number}</div>
        <div class="priority">Prioridade: ${ticket.priority?.name || 'Normal'}</div>
        <div class="date">${new Date().toLocaleString('pt-BR')}</div>
      </body>
    </html>
  `;
}

function App() {
  const [step, setStep] = useState<Step>('loading')
  const [priorities, setPriorities] = useState<Priority[]>([])
  const [services, setServices] = useState<Service[]>([])
  
  const [selectedPriority, setSelectedPriority] = useState<Priority | null>(null)
  const [ticketData, setTicketData] = useState<any>(null)
  
  const timeoutRef = useRef<number | null>(null)

  const [isConfigured, setIsConfigured] = useState(!!localStorage.getItem('sgsa_device_id') && !!localStorage.getItem('sgsa_api_url'))
  const [showSettings, setShowSettings] = useState(!isConfigured)

  useEffect(() => {
    if (!isConfigured) return;
    loadConfig()
  }, [isConfigured])

  const loadConfig = async () => {
    try {
      setStep('loading')
      const data = await fetchConfig()
      setPriorities(data.priorities)
      setServices(data.services)
      setStep('priority')
    } catch (err) {
      console.error(err)
      setStep('error')
      // Retry after 5s
      setTimeout(loadConfig, 5000)
    }
  }

  const handlePrioritySelect = (p: Priority) => {
    setSelectedPriority(p)
    setStep('service')
    resetTimeout()
  }

  const handleServiceSelect = async (s: Service) => {
    if (!selectedPriority) return
    
    try {
      setStep('loading')
      const result = await generateTicket(s.id, selectedPriority.id)
      setTicketData(result.ticket)
      setStep('printing')
      
      const printerName = localStorage.getItem('sgsa_printer')
      if (printerName && (window as any).api && (window as any).api.printTicket) {
        (window as any).api.printTicket(generatePrintHtml(result.ticket), printerName)
          .then((res: any) => {
            if (!res.success) console.error("Falha ao imprimir:", res.reason)
          }).catch(console.error)
      }
      
      // Auto reset after 5 seconds
      if (timeoutRef.current) clearTimeout(timeoutRef.current)
      timeoutRef.current = window.setTimeout(() => {
        resetToStart()
      }, 5000)
    } catch (err) {
      console.error(err)
      alert('Erro ao gerar senha.')
      resetToStart()
    }
  }

  const resetToStart = () => {
    setSelectedPriority(null)
    setTicketData(null)
    setStep('priority')
    if (timeoutRef.current) clearTimeout(timeoutRef.current)
  }

  const resetTimeout = () => {
    if (timeoutRef.current) clearTimeout(timeoutRef.current)
    timeoutRef.current = window.setTimeout(() => {
      resetToStart()
    }, 15000) // Idle timeout back to start if user leaves
  }

  if (showSettings) {
    return (
      <SettingsScreen 
        onSave={() => {
          setIsConfigured(true)
          setShowSettings(false)
        }} 
        onCancel={isConfigured ? () => setShowSettings(false) : undefined} 
      />
    )
  }

  return (
    <div className="totem-layout" onClick={resetTimeout}>
      <header className="totem-header">
        <h1>Bem-vindo</h1>
        <p>Retire sua senha para atendimento</p>
      </header>

      <main className="totem-content">
        {step === 'loading' && (
          <div className="spinner-border text-primary" style={{ width: '5rem', height: '5rem' }} role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
        )}

        {step === 'error' && (
          <div className="text-danger text-center">
            <i className="bi bi-exclamation-triangle" style={{ fontSize: '4rem' }}></i>
            <h2>Erro de Comunicação</h2>
            <p>Não foi possível conectar ao servidor.</p>
          </div>
        )}

        {step === 'priority' && (
          <>
            <h2 className="mb-5 text-secondary">1. Qual o tipo de atendimento?</h2>
            <div className="touch-grid">
              {priorities.map(p => (
                <button 
                  key={p.id} 
                  className={`btn-touch ${p.weight > 0 ? 'btn-priority' : ''}`}
                  onClick={() => handlePrioritySelect(p)}
                >
                  <i className={p.weight > 0 ? 'bi bi-person-wheelchair' : 'bi bi-person'}></i>
                  {p.name}
                </button>
              ))}
            </div>
          </>
        )}

        {step === 'service' && (
          <>
            <button className="btn btn-light border position-absolute top-0 start-0 m-4" onClick={resetToStart} style={{ fontSize: '1.5rem', padding: '1rem 2rem', borderRadius: '15px' }}>
              <i className="bi bi-arrow-left me-2"></i> Voltar
            </button>
            <h2 className="mb-5 text-secondary">2. Escolha o serviço desejado</h2>
            <div className="touch-grid">
              {services.map(s => (
                <button 
                  key={s.id} 
                  className="btn-touch"
                  onClick={() => handleServiceSelect(s)}
                >
                  <i className="bi bi-file-medical"></i>
                  {s.name}
                </button>
              ))}
            </div>
          </>
        )}

        {step === 'printing' && ticketData && (
          <div className="ticket-print-container">
            <h2>Sua senha é</h2>
            <div className="ticket-number">{ticketData.formatted_number}</div>
            <div className="ticket-service">{ticketData.service?.name}</div>
            <p className="mt-4 text-muted" style={{ fontSize: '1.5rem' }}>Aguarde ser chamado pela TV.</p>
            <div className="spinner-border text-secondary mt-3" role="status"></div>
          </div>
        )}
      </main>

      {/* Settings Button */}
      <button 
        onClick={() => setShowSettings(true)}
        style={{ position: 'fixed', bottom: 20, right: 20, background: 'transparent', border: 'none', fontSize: '24px', cursor: 'pointer', opacity: 0.3 }}
        title="Configurações"
      >
        ⚙️
      </button>
    </div>
  )
}

export default App
