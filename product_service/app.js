const express = require('express');
const cors = require('cors');
const productRouters = require('./routes/productRoutes');

const app = express();

app.use(cors());
app.use(express.json({ limit: '3mb' }));

// endpoint for healthcheck
app.get('/health', (req, res) => {
    res.json({
        status: "Ok",
        service: "product-services"
    });
});

app.use("/products", productRouters);

// Error handler untuk ukuran request terlalu besar
app.use((err, req, res, next) => {
    if (err.type === 'entity.too.large') {
        return res.status(413).json({
            message: "Gambar terlalu besar. Maksimal ukuran 3 MB."
        });
    }

    res.status(500).json({
        message: "Terjadi kesalahan pada server."
    });
});

// unknown path
app.use((req, res) => {
    res.status(404).json({
        message: "Endpoint tidak dikenal"
    });
});

module.exports = app;

