const passport = require('passport');
const bcrypt = require('bcrypt');
const User = require("../models/User");

module.exports = {
    loginView: (req, res) => {
        const csrfToken = req.csrfToken();
        res.render('auth/login', { csrfToken: csrfToken });
    },
    loginUser: (req, res, next) => {
        passport.authenticate('local', (err, user, info) => {
            if (err) return next(err);

            if (!user) {
                return res.redirect('/login?error');
            }

            req.logIn(user, (err) => {
                if (err) return next(err)
                switch (user.role) {
                    case 'news':
                        return res.redirect('/news/list');
                        break;
                    case 'calendar':
                        console.log("going to calendar");
                        return res.redirect('/calendar/list');
                        break;
                    default:
                        return res.redirect('/login?role');
                }
            });
        })(req, res, next);        
    },
    registerView: (req, res) => {
        const csrfToken = req.csrfToken();
        res.render('auth/register', { csrfToken: csrfToken });
    },
    registerUser: async (req, res) => {
        const { username, password } = req.body;
        if (!username || !password) {
            return res.render('auth/register', { error: 'Please fill all fields' });
        }

        if (await User.findOne({ where: { username } })) {
            return res.render('auth/register', { error: 'A user account already exists with this email' });
        }

        await User.create({ username, password: bcrypt.hashSync(password, 10) });

        res.redirect('/login?registrationDone');
    },
    generate: (req, res) => {
        const password = req.query.password
        const crypted = bcrypt.hash(password, 10, (err, hash) => {
            if (err) {
                return err.message
            }
            console.log('Hashed Password: ', hash);
            return hash
        });
        res.end(crypted);
    }
}