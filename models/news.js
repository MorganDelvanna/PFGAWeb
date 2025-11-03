const { Sequelize, DataTypes } = require('sequelize');
const sequelize = require('../util/db');

module.exports = sequelize.define(
    'News',
    {
        index: {
            type: DataTypes.INTEGER,
            autoIncrement: true,
            primaryKey: true,
        },
        date: {
            type: DataTypes.DATEONLY,
            allowNull: false,
            defaultValue: Sequelize.NOW
        },
        archived: {
            type: DataTypes.BOOLEAN,
            defaultValue: false,
        },
        header: {
            type: DataTypes.STRING,
            allowNull: false
        },
        description: {
            type: DataTypes.STRING,
            allowNull: true,
        }
    }, {
    timestamps: false
}
);