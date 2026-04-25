import { useEffect, useState, useRef } from 'react'
import { fetchConfig, createEventSource } from './services/api'
import { SettingsScreen } from './components/SettingsScreen'

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
  
  const [isConfigured, setIsConfigured] = useState(!!localStorage.getItem('sgsa_device_id') && !!localStorage.getItem('sgsa_api_url'))
  const [showSettings, setShowSettings] = useState(!isConfigured)

  // Elegant Airport-style Chime
  const playDing = () => {
    const audioCtx = new (window.AudioContext || (window as any).webkitAudioContext)()
    
    const playNote = (freq: number, startTime: number) => {
      const oscillator = audioCtx.createOscillator()
      const gainNode = audioCtx.createGain()
      
      // Soft sine wave for an elegant tone
      oscillator.type = 'sine'
      oscillator.frequency.value = freq
      
      // Envelope: Attack quickly, decay slowly
      gainNode.gain.setValueAtTime(0, startTime)
      gainNode.gain.linearRampToValueAtTime(0.6, startTime + 0.05)
      gainNode.gain.exponentialRampToValueAtTime(0.001, startTime + 1.5)
      
      oscillator.connect(gainNode)
      gainNode.connect(audioCtx.destination)
      
      oscillator.start(startTime)
      oscillator.stop(startTime + 1.5)
    }

    const now = audioCtx.currentTime;
    playNote(523.25, now);       // C5
    playNote(659.25, now + 0.25); // E5
    playNote(783.99, now + 0.50); // G5
  }

  // Text-to-Speech Voice Synthesizer
  const speakTicket = (ticket: Ticket) => {
    if ('speechSynthesis' in window) {
      window.speechSynthesis.cancel(); // Cancel any ongoing speech

      // Format ticket to be spelled out (e.g., "A-001" -> "A 0 0 1")
      const cleanNumber = ticket.formatted_number.replace('-', ' ');
      const spelledNumber = cleanNumber.split('').join(' ');
      
      const text = `Senha, ${spelledNumber}. Dirija-se ao, ${ticket.counter.name}.`;
      
      const utterance = new SpeechSynthesisUtterance(text);
      utterance.lang = 'pt-BR';
      utterance.rate = 0.85; // Slightly slower for better humanization and clarity
      utterance.pitch = 1.0;
      
      // Try to find a premium native voice if available in the OS/Browser
      const voices = window.speechSynthesis.getVoices();
      const ptVoice = voices.find(v => v.lang.includes('pt-BR') && (v.name.includes('Google') || v.name.includes('Microsoft Maria')));
      if (ptVoice) {
          utterance.voice = ptVoice;
      }
      
      // Wait for the chime to finish before speaking
      setTimeout(() => {
        window.speechSynthesis.speak(utterance);
      }, 1200);
    }
  }

  useEffect(() => {
    if (!isConfigured) return;

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
          speakTicket(newTicket)
          
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
  }, [isConfigured])

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
