import { useState, useEffect } from 'react'
import axios from 'axios'

function App() {
  const [users, setUsers] = useState([])
  const [health, setHealth] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [pagination, setPagination] = useState(null)
  const [activeTab, setActiveTab] = useState('users') // 'users' or 'health'

  useEffect(() => {
    fetchUsers()
    fetchHealth()
    
    // Auto-refresh health every 30 seconds
    const healthInterval = setInterval(fetchHealth, 30000)
    return () => clearInterval(healthInterval)
  }, [])

  const fetchUsers = async () => {
    try {
      setLoading(true)
      setError(null)
      
      // Call API Gateway endpoint
      const response = await axios.get('/api/v1/users')
      
      if (response.data.success) {
        setUsers(response.data.data)
        setPagination(response.data.pagination)
      } else {
        setError('Failed to fetch users')
      }
    } catch (err) {
      console.error('Error fetching users:', err)
      setError('Failed to connect to API. Please check if the services are running.')
    } finally {
      setLoading(false)
    }
  }

  const fetchHealth = async () => {
    try {
      // Call Health API endpoint
      const response = await axios.get('/api/v1/health')
      
      if (response.data.success) {
        setHealth(response.data)
      }
    } catch (err) {
      console.error('Error fetching health:', err)
      setHealth({
        success: false,
        status: 'error',
        data: { services: {}, summary: { total_services: 0, healthy_count: 0, unhealthy_count: 0, warning_count: 0 } }
      })
    }
  }

  const getStatusColor = (status) => {
    switch (status) {
      case 'healthy': return '#10b981'
      case 'warning': return '#f59e0b'
      case 'unhealthy': 
      case 'error': return '#ef4444'
      default: return '#6b7280'
    }
  }

  const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  return (
    <div className="container">
      <div className="header">
        <h1>🎁 GiftMakeBot Dashboard</h1>
        <p>Monitor your bot services and manage users</p>
        
        {/* Tab Navigation */}
        <div className="tab-navigation">
          <button 
            className={`tab-button ${activeTab === 'users' ? 'active' : ''}`}
            onClick={() => setActiveTab('users')}
          >
            👥 Users
          </button>
          <button 
            className={`tab-button ${activeTab === 'health' ? 'active' : ''}`}
            onClick={() => setActiveTab('health')}
          >
            💚 Health Monitor
          </button>
        </div>
      </div>

      {/* Users Tab */}
      {activeTab === 'users' && (
        <div className="tab-content">
          {loading ? (
            <div className="loading">
              <div className="spinner"></div>
              <p>Loading users...</p>
            </div>
          ) : error ? (
            <div className="error">
              <h2>⚠️ Error</h2>
              <p>{error}</p>
              <button onClick={fetchUsers} className="retry-button">
                🔄 Retry
              </button>
            </div>
          ) : (
            <>
              <div className="users-grid">
                {users.map(user => (
                  <div key={user.id} className="user-card">
                    <div className="user-header">
                      <img
                        src={user.avatar}
                        alt={user.name}
                        className="user-avatar"
                      />
                      <div className="user-info">
                        <h3>{user.name}</h3>
                        <p>{user.email}</p>
                      </div>
                    </div>
                    
                    <div className="user-details">
                      <div className="user-detail">
                        <strong>Role:</strong>
                        <span className="role-badge">{user.role}</span>
                      </div>
                      
                      <div className="user-detail">
                        <strong>Status:</strong>
                        <span className={`status-badge ${user.is_active ? 'status-active' : 'status-inactive'}`}>
                          {user.is_active ? 'Active' : 'Inactive'}
                        </span>
                      </div>
                      
                      <div className="user-detail">
                        <strong>ID:</strong>
                        <span>#{user.id}</span>
                      </div>
                      
                      <div className="user-detail">
                        <strong>Created:</strong>
                        <span>{formatDate(user.created_at)}</span>
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              {pagination && (
                <div className="pagination-info">
                  Showing <strong>{users.length}</strong> of <strong>{pagination.total}</strong> users 
                  (Page <strong>{pagination.current_page}</strong> of <strong>{pagination.total_pages}</strong>)
                </div>
              )}
            </>
          )}
          
          {users.length === 0 && !error && (
            <div style={{ textAlign: 'center', padding: '2rem', color: '#6b7280' }}>
              No users found.
            </div>
          )}
        </div>
      )}

      {/* Health Tab */}
      {activeTab === 'health' && (
        <div className="tab-content">
          {health ? (
            <div className="health-dashboard">
              {/* Overall Status */}
              <div className="health-overview">
                <div className="status-indicator">
                  <span 
                    className="status-dot"
                    style={{ backgroundColor: getStatusColor(health.status) }}
                  ></span>
                  <span className="status-text">
                    System Status: <strong style={{ color: getStatusColor(health.status) }}>
                      {health.status?.toUpperCase()}
                    </strong>
                  </span>
                </div>
                <p className="timestamp">Last checked: {formatDate(health.timestamp)}</p>
              </div>

              {/* Summary Cards */}
              {health.data?.summary && (
                <div className="summary-grid">
                  <div className="summary-card">
                    <h3>{health.data.summary.total_services}</h3>
                    <p>Total Services</p>
                  </div>
                  <div className="summary-card healthy">
                    <h3>{health.data.summary.healthy_count}</h3>
                    <p>Healthy</p>
                  </div>
                  <div className="summary-card warning">
                    <h3>{health.data.summary.warning_count}</h3>
                    <p>Warnings</p>
                  </div>
                  <div className="summary-card unhealthy">
                    <h3>{health.data.summary.unhealthy_count}</h3>
                    <p>Unhealthy</p>
                  </div>
                </div>
              )}

              {/* Services Status */}
              <div className="services-status">
                <h2>📊 Services Status</h2>
                <div className="services-grid">
                  {health.data?.services && Object.entries(health.data.services).map(([serviceName, service]) => (
                    <div key={serviceName} className="service-card">
                      <div className="service-header">
                        <span 
                          className="service-status-dot"
                          style={{ backgroundColor: getStatusColor(service.status) }}
                        ></span>
                        <h3>{serviceName}</h3>
                      </div>
                      <p className="service-message">{service.message}</p>
                      <div className="service-details">
                        <small>Last checked: {service.last_checked}</small>
                        {service.details && (
                          <div className="service-details-info">
                            {Object.entries(service.details).map(([key, value]) => (
                              <div key={key} className="detail-row">
                                <span className="detail-key">{key}:</span>
                                <span className="detail-value">
                                  {typeof value === 'object' ? JSON.stringify(value, null, 2) : String(value)}
                                </span>
                              </div>
                            ))}
                          </div>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              <div className="health-actions">
                <button onClick={fetchHealth} className="refresh-button">
                  🔄 Refresh Health Status
                </button>
              </div>
            </div>
          ) : (
            <div className="loading">
              <div className="spinner"></div>
              <p>Loading health status...</p>
            </div>
          )}
        </div>
      )}
    </div>
  )
}

export default App