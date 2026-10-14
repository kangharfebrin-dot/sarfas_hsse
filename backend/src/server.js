require('dotenv').config({ path: require('path').resolve(__dirname, '../.env') });
const express = require('express');
const cors = require('cors');
const morgan = require('morgan');

const apiRoutes = require('./routes/api');
const errorHandler = require('./middleware/errorHandler');

const app = express();
const PORT = Number(process.env.PORT) || 3001;

// CORS configuration
const allowedOrigins = [
  process.env.CORS_ORIGIN || 'http://localhost:8000',
  'http://127.0.0.1:8000'
];

app.use(cors({
  origin: (origin, callback) => {
    // allow requests with no origin (like mobile apps, curl, server-side requests)
    if (!origin || allowedOrigins.includes(origin)) {
      return callback(null, true);
    }
    return callback(null, true); // Dev convenience
  },
  credentials: true
}));

// Middlewares
app.use(morgan('dev'));
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Health Check
app.get('/health', (req, res) => {
  res.json({
    status: 'healthy',
    system: 'Checklist Sarfas HSSE API - PT Pertamina Patra Niaga FT Maos',
    timestamp: new Date().toISOString()
  });
});

// API Routes Version 1
app.use('/api/v1', apiRoutes);

// 404 Handler
app.use((req, res) => {
  res.status(404).json({
    success: false,
    message: `Endpoint ${req.method} ${req.originalUrl} tidak ditemukan.`
  });
});

// Central Error Handler
app.use(errorHandler);

// Start Server
if (process.env.NODE_ENV !== 'test') {
  app.listen(PORT, () => {
    console.log(`=======================================================`);
    console.log(`  CHECKLIST SARFAS HSSE - PT PERTAMINA PATRA NIAGA`);
    console.log(`  Fuel Terminal Maos Backend API Server`);
    console.log(`  Status  : Running on http://localhost:${PORT}`);
    console.log(`  Base API: http://localhost:${PORT}/api/v1`);
    console.log(`=======================================================`);
  });
}

module.exports = app;
