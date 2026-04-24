import { useEffect, useState, useRef } from 'react'
import { fetchConfig, createEventSource } from './services/api'

interface Ticket {
  id: string
  formatted_number: string
  priority: { weight: number; name: string }
  service: { name: string }
  counter: { name: string }
}

function App() {
  const [currentTicket, setCurrentTicket] = useState<Ticket | null>(null)
  const [history, setHistory] = useState<Ticket[]>([])
  const [isFlashing, setIsFlashing] = useState(false)
  const flashTimeout = useRef<number | null>(null)

  // Web Audio API Ding
  const playDing = () => {
    const audioCtx = new (window.AudioContext || (window as any).webkitAudioContext)()
    const oscillator = audioCtx.createOscillator()
    const gainNode = audioCtx.createGain()
    
    oscillator.type = 'sine'
    oscillator.frequency.setValueAtTime(880, audioCtx.currentTime) // A5
    oscillator.frequency.exponentialRampToValueAtTime(440, audioCtx.currentTime + 0.5) // Drop to A4
    
    gainNode.gain.setValueAtTime(1, audioCtx.currentTime)
    gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 1)
    
    oscillator.connect(gainNode)
    gainNode.connect(audioCtx.destination)
    
    oscillator.start()
    oscillator.stop(audioCtx.currentTime + 1)
  }

  useEffect(() => {
    // Initial fetch to get history
    const loadConfig = async () => {
      try {
        const data = await fetchConfig()
        if (data.history && data.history.length > 0) {
          setCurrentTicket(data.history[0])
          setHistory(data.history.slice(1, 5))
        }
      } catch (error) {
        console.error('Failed to load TV config:', error)
      }
    }
    
    loadConfig()

    // Connect to SSE
    const sse = createEventSource()
    
    sse.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data)
        if (data.action === 'call') {
          const newTicket = data.ticket as Ticket
          
          playDing()
          
          // Trigger CSS Flash animation
          setIsFlashing(true)
          if (flashTimeout.current) clearTimeout(flashTimeout.current)
          flashTimeout.current = window.setTimeout(() => setIsFlashing(false), 1500)
          
          setCurrentTicket((prev) => {
            if (prev) {
              setHistory((h) => [prev, ...h].slice(0, 4))
            }
            return newTicket
          })
        }
      } catch (err) {
        console.error('SSE Error processing message', err)
      }
    }

    sse.onerror = () => {
      console.log('SSE connection lost. Reconnecting...')
      // EventSource reconnects automatically
    }

    return () => {
      sse.close()
    }
  }, [])

  return (
    <div className="tv-layout">
      {/* Lado Esquerdo - Chamada Principal */}
      <div className={`tv-main ${isFlashing ? 'flash-active' : ''}`}>
        {currentTicket ? (
          <>
            {currentTicket.priority.weight > 0 && (
              <div className="ticket-priority">
                PRIORIDADE
              </div>
            )}
            <div className="ticket-number">
              {currentTicket.formatted_number}
            </div>
            <div className="ticket-counter">
              Dirija-se ao {currentTicket.counter.name}
            </div>
          </>
        ) : (
          <div className="ticket-counter">Aguardando Chamadas...</div>
        )}
      </div>

      {/* Lado Direito - Histórico */}
      <div className="tv-history">
        <div className="history-header">
          <h2>Últimas Chamadas</h2>
        </div>
        <div className="history-list">
          {history.map((ticket, idx) => (
            <div key={`${ticket.id}-${idx}`} className="history-item">
              <div className="h-number">{ticket.formatted_number}</div>
              <div className="h-counter">{ticket.counter.name}</div>
            </div>
          ))}
          
          {history.length === 0 && (
            <div className="text-center text-secondary mt-5">
              Nenhuma chamada recente
            </div>
          )}
        </div>
      </div>
    </div>
  )
}

export default App
