import { useEffect, useState, useRef } from 'react'
import { auth, attendant } from './services/api'
import { SettingsScreen } from './components/SettingsScreen'

interface User {
  id: string
  name: string
  email: string
}

interface Counter {
  id: string
  name: string
  area: { name: string; unit: { name: string } }
}

interface Ticket {
  id: string
  formatted_number: string
  status: string
  priority: { name: string }
  service: { name: string }
}

function App() {
  const [user, setUser] = useState<User | null>(null)
  const [counters, setCounters] = useState<Counter[]>([])
  const [selectedCounter, setSelectedCounter] = useState<Counter | null>(null)
  
  const [currentTicket, setCurrentTicket] = useState<Ticket | null>(null)
  const [pendingCount, setPendingCount] = useState<number>(0)
  
  const [debugError, setDebugError] = useState<string>('')

  // Polling intervalm
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')

  // Polling interval
  const pollingRef = useRef<number | null>(null)

  const [isConfigured, setIsConfigured] = useState(!!localStorage.getItem('sgsa_api_url'))
  const [showSettings, setShowSettings] = useState(!isConfigured)

  useEffect(() => {
    if (!isConfigured) return;

    const storedUser = auth.getUser()
    if (storedUser) {
      setUser(storedUser)
      loadCounters()
    }
  }, [])

  useEffect(() => {
    if (selectedCounter) {
      document.title = `SGSA Guichê: ${selectedCounter.name}`
      fetchState()
      pollingRef.current = window.setInterval(fetchState, 5000)
    } else {
      document.title = 'SGSA Guichê'
      if (pollingRef.current) clearInterval(pollingRef.current)
    }
    return () => {
      if (pollingRef.current) clearInterval(pollingRef.current)
    }
  }, [selectedCounter])

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault()
    try {
      setError('')
      setDebugError('')
      const data = await auth.login({ email, password })
      setUser(data.user)
      loadCounters()
    } catch (err: any) {
      setError(err.response?.data?.message || err.message || 'Erro ao conectar')
      
      // Capture advanced debug information
      const debugInfo = {
        name: err.name,
        message: err.message,
        code: err.code,
        status: err.response?.status,
        url: err.config?.url,
        baseURL: err.config?.baseURL,
        method: err.config?.method,
      };
      setDebugError(JSON.stringify(debugInfo, null, 2))
    }
  }

  const handleLogout = async () => {
    await auth.logout()
    setUser(null)
    setSelectedCounter(null)
  }

  const loadCounters = async () => {
    try {
      const data = await attendant.getCounters()
      setCounters(data)
    } catch (err) {
      console.error(err)
    }
  }

  const fetchState = async () => {
    if (!selectedCounter) return
    try {
      const data = await attendant.getState(selectedCounter.id)
      setCurrentTicket(data.current_ticket)
      setPendingCount(data.pending_count)
    } catch (err) {
      console.error(err)
    }
  }

  const handleCallNext = async () => {
    if (!selectedCounter) return
    try {
      const data = await attendant.callNext(selectedCounter.id)
      setCurrentTicket(data.ticket)
      fetchState()
    } catch (err: any) {
      alert(err.response?.data?.message || 'Erro ao chamar próximo')
    }
  }

  const handleAction = async (action: 'recall' | 'start' | 'finish' | 'absent') => {
    if (!currentTicket) return
    try {
      await attendant[action](currentTicket.id)
      fetchState()
    } catch (err) {
      alert('Erro ao executar ação')
    }
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

  // --- RENDER LOGIN ---
  if (!user) {
    return (
      <div className="auth-wrapper">
        <div className="auth-card">
          <h2>SGSA Login</h2>
          {error && <div className="alert alert-danger">{error}</div>}
          <form onSubmit={handleLogin}>
            <div className="mb-3">
              <label className="form-label">E-mail</label>
              <input type="email" className="form-control form-control-lg" value={email} onChange={e => setEmail(e.target.value)} required />
            </div>
            <div className="mb-4">
              <label className="form-label">Senha</label>
              <input type="password" className="form-control form-control-lg" value={password} onChange={e => setPassword(e.target.value)} required />
            </div>
            <button type="submit" className="btn btn-primary btn-lg w-100">Entrar</button>
          </form>
          
          {debugError && (
            <div className="mt-4 p-2 bg-dark text-warning rounded text-start" style={{ fontSize: '11px', whiteSpace: 'pre-wrap', fontFamily: 'monospace' }}>
              <strong>DEBUG INFO:</strong><br />
              {debugError}
            </div>
          )}

          <button 
            className="btn btn-link w-100 mt-3 text-secondary text-decoration-none" 
            onClick={() => setShowSettings(true)}
          >
            <i className="bi bi-gear me-1"></i> Configurações do Sistema
          </button>
        </div>
      </div>
    )
  }

  // --- RENDER COUNTER SELECTION ---
  if (!selectedCounter) {
    return (
      <div className="auth-wrapper">
        <div className="auth-card" style={{ maxWidth: '600px' }}>
          <h2>Selecione sua Mesa / Guichê</h2>
          <div className="list-group">
            {counters.map(c => (
              <button key={c.id} className="list-group-item list-group-item-action py-3" onClick={() => setSelectedCounter(c)}>
                <div className="d-flex w-100 justify-content-between">
                  <h5 className="mb-1 fw-bold">{c.name}</h5>
                </div>
                <p className="mb-1 text-muted">{c.area.name} - {c.area.unit.name}</p>
              </button>
            ))}
          </div>
          <button className="btn btn-light w-100 mt-4" onClick={handleLogout}>Sair</button>
        </div>
      </div>
    )
  }

  // --- RENDER DASHBOARD ---
  return (
    <div className="dashboard-layout">
      <header className="top-navbar">
        <div className="d-flex align-items-center gap-3">
          <i className="bi bi-display text-primary fs-3"></i>
          <div>
            <h5 className="mb-0 fw-bold">{selectedCounter.name}</h5>
            <small className="text-muted">{selectedCounter.area.name}</small>
          </div>
        </div>
        <div className="user-info d-flex align-items-center gap-3">
          <span>Olá, {user.name}</span>
          <button className="btn btn-sm btn-outline-danger" onClick={() => setSelectedCounter(null)}>Trocar Mesa</button>
        </div>
      </header>

      <main className="dashboard-content">
        <div className="dashboard-controls">
          <div className="stat-card">
            <h3>Pessoas na Fila</h3>
            <div className="stat-number">{pendingCount}</div>
          </div>
          
          <button 
            className="action-btn btn-call-next mt-3"
            onClick={handleCallNext}
            disabled={currentTicket?.status === 'in_progress' || currentTicket?.status === 'called'}
          >
            <i className="bi bi-megaphone"></i>
            Chamar Próximo
          </button>
        </div>

        {currentTicket ? (
          <div className="current-ticket-card">
            <div className="header">Em Atendimento</div>
            <div className="ticket-number">{currentTicket.formatted_number}</div>
            
            <div className="ticket-details">
              <div className="detail-item">
                <div className="label">Prioridade</div>
                <div className="value text-danger">{currentTicket.priority.name}</div>
              </div>
              <div className="detail-item">
                <div className="label">Serviço</div>
                <div className="value">{currentTicket.service.name}</div>
              </div>
              <div className="detail-item">
                <div className="label">Status</div>
                <div className="value text-uppercase">{currentTicket.status}</div>
              </div>
            </div>
            
            <div className="ticket-actions">
              <button 
                className="btn btn-outline-primary" 
                onClick={() => handleAction('recall')}
                disabled={currentTicket.status !== 'called'}
              >
                <i className="bi bi-arrow-repeat"></i> Rechamar na TV
              </button>
              
              {currentTicket.status === 'called' ? (
                <>
                  <button className="btn btn-success" onClick={() => handleAction('start')}>
                    <i className="bi bi-play-fill"></i> Iniciar Atend.
                  </button>
                  <button className="btn btn-danger" onClick={() => handleAction('absent')}>
                    <i className="bi bi-person-x"></i> Não Compareceu
                  </button>
                </>
              ) : (
                <button className="btn btn-dark" onClick={() => handleAction('finish')}>
                  <i className="bi bi-check-lg"></i> Finalizar Atend.
                </button>
              )}
            </div>
          </div>
        ) : (
          <div className="empty-state">
            <i className="bi bi-check-circle"></i>
            <h2>Nenhum atendimento ativo</h2>
            <p>Clique em "Chamar Próximo" para iniciar.</p>
          </div>
        )}
      </main>
    </div>
  )
}

export default App
