const knex = require('knex');
const knexConfig = require('../database/knexfile');

const environment = process.env.NODE_ENV || 'development';
const db = knex(knexConfig[environment]);

module.exports = db;
