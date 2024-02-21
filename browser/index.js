require('dotenv').config({ path: '.env' })
const http = require('http')
const express = require('express');
const app = express();
const bodyParser = require('body-parser');
const route = require('./src/routes/browser')

app.use(
    (req, res, next) => {
        next()
    },
    bodyParser.json(),
    bodyParser.urlencoded({ extended: true }),
)

app.use(route);

const server = http.createServer(app)
server.listen(4000, () => console.log(`Running at :${process.env.PORT || 4000}...`))
