const mysql = require('mysql2/promise');
require('dotenv').config();


const pool = mysql.createPool({
    host: process.env.DB_HOST  || 'Localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '""',
    database: process.env.DB_NAME || 'product_db',
    waitForConnection: true,
    connectionLimit: 10,
});

module.exports = pool;