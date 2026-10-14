const mysql = require('mysql2/promise');
require('dotenv').config({ path: require('path').resolve(__dirname, '../../.env') });

async function checkDatabase() {
  const host = process.env.DB_HOST || '127.0.0.1';
  const port = Number(process.env.DB_PORT) || 3306;
  const user = process.env.DB_USER || 'root';
  const password = process.env.DB_PASSWORD || '';
  const dbName = process.env.DB_NAME || 'checklist_sarfas_hsse';

  console.log(`Connecting to MySQL server at ${host}:${port} as ${user}...`);
  try {
    const connection = await mysql.createConnection({ host, port, user, password });
    console.log('✓ Successfully connected to MySQL server!');
    
    await connection.query(`CREATE DATABASE IF NOT EXISTS \`${dbName}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`);
    console.log(`✓ Database \`${dbName}\` created or already exists.`);
    
    await connection.end();
    return true;
  } catch (error) {
    console.error('✗ Failed to connect/create database:', error.message);
    return false;
  }
}

checkDatabase().then(success => {
  process.exit(success ? 0 : 1);
});
