const express = require('express');
const router = express.Router();
const newsListController = require('../controllers/newsList');
const { protectRoute } = require('../auth');

router.get('/', newsListController.newsView);
router.get('/list', protectRoute({ route: 'news' }), newsListController.listView);
router.get('/addNews', protectRoute({ route: 'news' }), newsListController.addNew);
router.get('/edit', protectRoute({ route: 'news' }), newsListController.edit);
router.post('/archive', protectRoute({ route: 'news' }), newsListController.archive);
router.post('/saveNews', protectRoute({ route: 'news' }), newsListController.saveNew);
router.post('/update', protectRoute({ route: 'news' }), newsListController.update);

module.exports = router;