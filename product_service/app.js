const express = require('express');
const cors = require('cors');
const productRouters = require('./routes/productRoutes');


const app = express();


app.use(cors());
app.use(express.json());

//endpoint for healthcheck
app.get('/health', (req, res) => {
    res.json({
        status: "Ok",
        service: "product-services"
    });

});

app.use("/products", productRouters);

//unknow path
app.use((req,res) => {
    res.status(404).json({
        message: "Endpoint tidak dikenal"
    });
});

module.exports = app;