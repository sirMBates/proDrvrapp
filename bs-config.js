module.exports = {
    proxy: "https://prodriver.local",

    port: 3000,

    https: {
        key: "certs/prodriver.local-key.pem",
        cert: "certs/prodriver.local.pem"
    },

    files: [
        "app/**/*.php",
        "public/**/*.php",
        "public/dist/**/*.css",
        "public/dist/**/*.js"
    ],

    open: false,

    notify: true
};