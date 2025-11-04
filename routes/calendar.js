const express = require('express');
const router = express.Router();
const CalController = require('../controllers/calendar');
const { protectRoute } = require('../auth');

router.get('/', CalController.calView);
router.get('/list', protectRoute({ route: 'calendar' }), CalController.listView);
router.get('/addEvent', protectRoute({ route: 'calendar' }), CalController.addNew);
router.post('/saveNew', protectRoute({ route: 'calendar' }), CalController.saveNew);
router.get('/edit', protectRoute({ route: 'calendar' }), CalController.edit);
router.post('/update', protectRoute({ route: 'calendar' }), CalController.update);

module.exports = router;
