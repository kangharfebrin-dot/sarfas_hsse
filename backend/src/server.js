require('dotenv').config({ path: require('path').resolve(__dirname, '../.env') });
const express = require('express');
const cors = require('cors');
const morgan = require('morgan');

const helmet = require('helmet');
const rateLimit = require('express-rate-limit');

const apiRoutes = require('./routes/api');
const errorHandler = require('./middleware/errorHandler');

// Validate JWT_SECRET on startup
if (!process.env.JWT_SECRET || process.env.JWT_SECRET.trim().length < 16) {
  if (process.env.NODE_ENV === 'production') {
    console.error('FATAL KEAMANAN: JWT_SECRET wajib dikonfigurasi di .env dengan panjang minimal 16 karakter pada mode produksi.');
    process.exit(1);
  } else {
    console.warn('[PERINGATAN KEAMANAN]: JWT_SECRET belum dikonfigurasi atau terlalu pendek di .env backend.');
  }
}

const app = express();
const PORT = Number(process.env.PORT) || 3001;

// 1. Security Headers (Helmet)
app.use(helmet());

// 2. CORS configuration (Strict Origin Validation)
const allowedOrigins = (process.env.CORS_ORIGIN ? process.env.CORS_ORIGIN.split(',') : [
  'http://localhost:8000',
  'http://127.0.0.1:8000'
]).map(origin => origin.trim().replace(/\/$/, ''));

app.use(cors({
  origin: (origin, callback) => {
    // allow requests with no origin (such as server-to-server HTTP client in Laravel or curl)
    if (!origin) {
      return callback(null, true);
    }
    const cleanOrigin = origin.trim().replace(/\/$/, '');
    if (allowedOrigins.includes(cleanOrigin)) {
      return callback(null, true);
    }
    return callback(new Error(`Akses ditolak: Origin '${origin}' tidak diizinkan oleh kebijakan CORS.`));
  },
  credentials: true
}));

// 3. Rate Limiting
const globalLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: 1000,
  standardHeaders: true,
  legacyHeaders: false,
  message: { success: false, message: 'Terlalu banyak permintaan dari IP ini, silakan coba beberapa saat lagi.' }
});
app.use(globalLimiter);

const loginLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: 30, // 30 login attempts per 15 minutes
  standardHeaders: true,
  legacyHeaders: false,
  message: { success: false, message: 'Terlalu banyak percobaan login. Silakan coba kembali setelah 15 menit.' }
});
app.use('/api/v1/auth/login', loginLimiter);

// 4. Middlewares
app.use(morgan(process.env.NODE_ENV === 'production' ? 'combined' : 'dev'));
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
