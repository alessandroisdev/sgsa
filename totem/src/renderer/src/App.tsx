import { useEffect, useState, useRef } from 'react'
import { fetchConfig, generateTicket } from './services/api'

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

function App() {
  const [step, setStep] = useState<Step>('loading')
  const [priorities, setPriorities] = useState<Priority[]>([])
  const [services, setServices] = useState<Service[]>([])
  
  const [selectedPriority, setSelectedPriority] = useState<Priority | null>(null)
  const [ticketData, setTicketData] = useState<any>(null)
  
  const timeoutRef = useRef<number | null>(null)

  useEffect(() => {
    loadConfig()
  }, [])

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
    </div>
  )
}

export default App
