const { Sequelize, DataTypes } = require('sequelize');
const sequelize = require('../util/db');

module.exports = sequelize.define(
    'User',
    {
        id: {
            type: DataTypes.INTEGER,
            autoIncrement: true,
            allowNull: false,
            primaryKey: true
        },
        username: {
            type: DataTypes.STRING
        },
        password: {
            type: DataTypes.STRING
        },
        role: {
            type: DataTypes.STRING
        }
    }, {
    timestamps: false,
    tableName: 'node_users'
}
);