const createError = require('http-errors');
const express = require('express');
const logger = require('morgan');
const path = require('path');
const db = require('./util/db.js');
const cookieParser = require('cookie-parser');
const csrf = require('csurf');
const passport = require('passport');
const session = require('express-session');
const { init: initAuth } = require('./auth');
const newsRoutes = require('./routes/news');
const authRoutes = require('./routes/auth');
const calRoutes = require('./routes/calendar');


const app = express();

// view engine setup
app.set('views', path.join(__dirname, 'views'));
app.set('view engine', 'pug');

app.use(logger('dev'));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

app.use((req, res, next) => {
    res.setHeader('Access-Control-Allow-Origin', 'http://localhost'); // Or '*' for all origins
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    next();
});
app.use(cookieParser());
app.use(session({
    secret: 'tcw9wnq@yug6pytBQA',
    saveUninitialized: true,
    resave: true,
    cookie: {
        secure: process.env.NODE_ENV === 'production',
        sameSite: 'strict',  // or 'lax' for more compatibility
        httpOnly: true,       // Prevents JavaScript access to session cookie
        maxAge: 24 * 60 * 60 * 1000
    }
}));

app.use(passport.initialize());
app.use(passport.session());

const csrfProtection = csrf({
    cookie: {
        httpOnly: true, // CSRF cookie can't be accessed via JavaScript
        secure: process.env.NODE_ENV === 'production',
        sameSite: 'strict'
    }
});
app.use(csrfProtection);
initAuth();

app.use('/news/', newsRoutes);
app.use('/calendar/', calRoutes);
app.use('/', authRoutes);

// catch 404 and forward to error handler
app.use(function (req, res, next) {
    next(createError(404));
});

// error handler
app.use(function (err, req, res, next) {
    // set locals, only providing error in development
    res.locals.message = err.message;
    res.locals.error = req.app.get('env') === 'development' ? err : {};

    // render the error page
    res.status(err.status || 500);
    res.render('error');
});

// Test that the DB is accessible
db.sync({ force: false })
    .then(() => {

    })
    .catch((error) => { res.render('error'); }); 



module.exports = app;