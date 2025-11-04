const calItem = require('../models/calendar');
const { Op } = require('sequelize');
const moment = require('moment');

module.exports = {
    // return jason for full calendar
    calView: async (req, res) => {
        var targetDate = new Date();
        targetDate.setDate(0);
        targetDate.setDate(1);
        const result = await calItem.findAll({
            where: {
                EventStart: { 
                    [Op.gte]: targetDate,
                }
            }
        });
        res.json(result);
    },
    // List of existing calendar events
    listView: async (req, res) => {
        const result = await calItem.findAll({
            order: [
                ['EventStart', 'DESC'] 
            ]
        });
        res.render('calendar/list', {
            title: 'Calendar List',
            events: result,
            moment
        });
    },
    // Add new Calendar Event Item
    addNew: async (req, res) => {
        const csrfToken = req.csrfToken();
        res.render('calendar/new', {
            title: 'Add New Event',
            csrfToken: csrfToken
        });
    },
    // save new News Item
    saveNew: async (req, res) => {
        const { eventStart, eventEnd, title, description } = req.body;
        const item = await calItem.create({ eventStart: eventStart, eventEnd: eventEnd, title: title, description: description });
        res.redirect('/calendar/list');
    },
    // Edit an existing News item
    edit: async (req, res) => {
        const { id } = req.query;

        const csrfToken = req.csrfToken();
        const instance = await calItem.findOne({ where: { id: id } });
        res.render('calendar/edit', {
            title: 'Edit Calendar Event',
            csrfToken: csrfToken,            
            id: instance.id,
            eventStart: instance.eventStart,
            eventEnd: instance.eventEnd,
            title: instance.title,
            description: instance.description
        });
    },
    // save updated News Item
    update: async (req, res) => {
        const { id, eventStart, eventEnd, title, description } = req.body
        const instance = await calItem.findOne({ where: { id: id } });

        if (instance) {
            instance.eventStart = eventStart
            instance.eventEnd = eventEnd
            instance.title = title
            instance.description = description
        }
        await instance.save();
        res.redirect('/calendar/list');
    }

}