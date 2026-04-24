import React, { useState } from 'react';
import { updateApiConfig } from '../services/api';

interface SettingsScreenProps {
  onSave: () => void;
  onCancel?: () => void;
}

export const SettingsScreen: React.FC<SettingsScreenProps> = ({ onSave, onCancel }) => {
  const [url, setUrl] = useState(localStorage.getItem('sgsa_api_url') || import.meta.env.VITE_API_URL || 'http://localhost:8084/api/v1');
  const [deviceId, setDeviceId] = useState(localStorage.getItem('sgsa_device_id') || import.meta.env.VITE_DEVICE_ID || '');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!url || !deviceId) {
      alert("Por favor, preencha todos os campos.");
      return;
    }
    updateApiConfig(url, deviceId);
    onSave();
  };

  return (
    <div style={styles.container}>
      <div style={styles.card}>
        <h2 style={styles.title}>⚙️ Configuração do Totem</h2>
        <p style={styles.subtitle}>Configure os parâmetros de conexão do terminal de autoatendimento.</p>
        
        <form onSubmit={handleSubmit}>
          <div style={styles.formGroup}>
            <label style={styles.label}>URL da API</label>
            <input 
              type="text" 
              style={styles.input} 
              value={url} 
              onChange={e => setUrl(e.target.value)} 
              placeholder="http://192.168.0.x:8084/api/v1" 
              required 
            />
          </div>
          
          <div style={styles.formGroup}>
            <label style={styles.label}>ID do Dispositivo (UUID)</label>
            <input 
              type="text" 
              style={styles.input} 
              value={deviceId} 
              onChange={e => setDeviceId(e.target.value)} 
              placeholder="Ex: 019dc0ed..." 
              required 
            />
          </div>
          
          <div style={styles.buttonGroup}>
            {onCancel && (
              <button type="button" style={styles.btnCancel} onClick={onCancel}>Cancelar</button>
            )}
            <button type="submit" style={styles.btnSave}>Salvar Configuração</button>
          </div>
        </form>
      </div>
    </div>
  );
};

const styles = {
  container: {
    position: 'fixed' as const,
    top: 0, left: 0, right: 0, bottom: 0,
    backgroundColor: '#f8f9fa',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 9999,
  },
  card: {
    backgroundColor: 'white',
    padding: '40px',
    borderRadius: '16px',
    boxShadow: '0 10px 30px rgba(0,0,0,0.1)',
    width: '100%',
    maxWidth: '500px',
  },
  title: {
    margin: '0 0 10px 0',
    color: '#212529',
    fontSize: '24px',
    fontWeight: 'bold',
  },
  subtitle: {
    margin: '0 0 30px 0',
    color: '#6c757d',
    fontSize: '14px',
  },
  formGroup: {
    marginBottom: '20px',
  },
  label: {
    display: 'block',
    marginBottom: '8px',
    color: '#495057',
    fontWeight: '600',
    fontSize: '14px',
  },
  input: {
    width: '100%',
    padding: '12px 16px',
    fontSize: '16px',
    border: '2px solid #dee2e6',
    borderRadius: '8px',
    boxSizing: 'border-box' as const,
    outline: 'none',
  },
  buttonGroup: {
    display: 'flex',
    gap: '10px',
    marginTop: '30px',
  },
  btnSave: {
    flex: 1,
    padding: '14px',
    backgroundColor: '#0d6efd',
    color: 'white',
    border: 'none',
    borderRadius: '8px',
    fontSize: '16px',
    fontWeight: 'bold',
    cursor: 'pointer',
  },
  btnCancel: {
    padding: '14px 20px',
    backgroundColor: '#f8f9fa',
    color: '#495057',
    border: '2px solid #dee2e6',
    borderRadius: '8px',
    fontSize: '16px',
    fontWeight: 'bold',
    cursor: 'pointer',
  }
};
