const passport = require('passport');
const LocalStrategy = require('passport-local');
const bcrypt = require('bcrypt');
const User = require("./models/User");

module.exports = {
    init: () => {
        passport.use(
            new LocalStrategy({ usernameField: 'username' }, async (username, password, done) => {
                const user = await User.findOne({ where: { username } });

                if (!user) {
                    return done(null, false);
                }
                if (!bcrypt.compareSync(password, user.password)) {
                    return done(null, false);
                }
                return done(null, user);
            })
        );
        passport.serializeUser((user, done) => {
            ;
            done(null, user.id);
        });

        passport.deserializeUser(async (id, done) => {
            const user = await User.findOne({ where: { id } });
            done(null, user);
        });
    },

    protectRoute:
        (options = {}) =>
        (req, res, next) => {
            if (req.isAuthenticated()) {
                if (req.user.role == options.route) {
                    return next();
                } else {
                    res.redirect('/login?next=' + req.url);
                }               
            }
            res.redirect('/login?next=' + req.url);
    },
};