const { Sequelize } = require('sequelize');

module.exports = new Sequelize('pfga_forum', 'root', '1q2w3e4r',
    {
        dialect: 'mariadb',
        host: 'localhost',
        port: 3306,
        showWarnings: true,
        connectTimeout: 1000,
        pool: {
            "max": 10,
            "min": 0,
            "idle": 25000,
            "acquire": 25000,
            "requestTimeout": 300000
        },
        dialectOptions: {
            options: { encrypt: true }
        },
        timezone: '-05:00'
    });