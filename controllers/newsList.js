const NewsItem = require('../models/news');
const moment = require('moment');

module.exports = {
    // View for listing News on the index page
    newsView: async (req, res) => {
        const result = await NewsItem.findAll({ where: { archived: false } });
        res.render('news/news', { news: result });
    },
    listView: async (req, res) => {
        const result = await NewsItem.findAll();
        const csrfToken = req.csrfToken();
        res.render('news/list', {
            title: 'News List',
            news: result,
            csrfToken: csrfToken,
            moment
        });
    },
    addNew: async (req, res) => {
        const csrfToken = req.csrfToken();
        res.render('news/new', {
            title: 'Add New Item',
            csrfToken: csrfToken
        });
    },
    edit: async (req, res) => {
        const { id } = req.query;

        const csrfToken = req.csrfToken();
        const instance = await NewsItem.findOne({ where: { index: id } });
        res.render('news/edit', {
            title: 'Edit News Item',
            csrfToken: csrfToken,
            header: instance.header,
            description: instance.description,
            archived: instance.archived,
            index: instance.index
        });
    },
    archive: async (req, res) => {
        const { id } = req.body;
        const instance = await NewsItem.findOne({ where: { index: id } });

        if (instance) {
            instance.archived = true;
        }

        await instance.save();
        res.send({ success: true, message: 'NewsItem Updated' });
    },
    saveNew: async (req, res) => {
        const { header, description } = req.body;
        const item = await NewsItem.create({ header: header, description: description });
        res.redirect('/news/list');
    },
    update: async (req, res) => {
        const { header, description, archived, index } = req.body
        const instance = await NewsItem.findOne({ where: { index: index } });
        const isArchived = (typeof archived !== 'undefined');

        if (instance) {
            instance.header = header,
                instance.description = description,
                instance.archived = isArchived
        }

        await instance.save();
        res.redirect('/news/list');
    }
}