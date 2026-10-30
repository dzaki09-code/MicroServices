
const productModel = require('../models/productModel');

const MAX_IMAGE_SIZE = 2 * 1024 * 1024; // 2 MB

// Validasi gambar Base64
function validateImage(image) {
    if (!image || typeof image !== 'string') {
        return 'Image wajib diisi dan harus berupa string Base64';
    }

    // Memastikan format Base64 standar dan panjangnya sesuai
    const base64Regex = /^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$/;

    if (!base64Regex.test(image) || image.length === 0) {
        return 'Format image Base64 tidak valid';
    }

    // Decode Base64 menjadi buffer
    const imageBuffer = Buffer.from(image, 'base64');

    // Memastikan Base64 benar-benar valid
    if (imageBuffer.toString('base64') !== image) {
        return 'Format image Base64 tidak valid';
    }

    // Memeriksa ukuran gambar asli setelah decode
    if (imageBuffer.length > MAX_IMAGE_SIZE) {
        return 'Ukuran image maksimal 2 MB';
    }

    return null;
}

// GET semua produk
async function index(req, res) {
    try {
        const products = await productModel.getAllProducts();

        res.status(200).json({
            message: 'Berhasil mengambil data produk',
            data: products
        });
    } catch (error) {
        res.status(500).json({
            message: 'Gagal mengambil data produk',
            error: error.message
        });
    }
}

// GET produk berdasarkan ID
async function GetProductById(req, res) {
    try {
        const { id } = req.params;
        const products = await productModel.getProductById(id);

        if (products.length === 0) {
            return res.status(404).json({
                message: 'Produk atau data tidak ditemukan'
            });
        }

        res.status(200).json({
            message: 'Berhasil mengambil data berdasarkan ID',
            data: products[0]
        });
    } catch (error) {
        res.status(500).json({
            message: 'Gagal mengambil data berdasarkan ID',
            error: error.message
        });
    }
}

// POST tambah produk
async function CreateProduct(req, res) {
    try {
        const { name, description, price, stock, image } = req.body;

        if (!name || price == null || stock == null) {
            return res.status(400).json({
                message: 'Name, price, dan stock wajib diisi'
            });
        }

        const imageError = validateImage(image);

        if (imageError) {
            return res.status(400).json({
                message: imageError
            });
        }

        const newProduct = await productModel.createProducts({
            name,
            description,
            price,
            stock,
            image
        });

        res.status(201).json({
            message: 'Produk berhasil ditambahkan',
            data: newProduct
        });
    } catch (error) {
        res.status(500).json({
            message: 'Gagal menambahkan produk',
            error: error.message
        });
    }
}

// PUT update produk
async function UpdateProduct(req, res) {
    try {
        const { id } = req.params;
        const { name, description, price, stock, image } = req.body;

        if (!name || price == null || stock == null) {
            return res.status(400).json({
                message: 'Name, price, dan stock wajib diisi'
            });
        }

        const imageError = validateImage(image);

        if (imageError) {
            return res.status(400).json({
                message: imageError
            });
        }

        const existingProduct = await productModel.getProductById(id);

        if (existingProduct.length === 0) {
            return res.status(404).json({
                message: 'Produk atau data tidak ditemukan'
            });
        }

        const updatedProduct = await productModel.updateProduct(id, {
            name,
            description,
            price,
            stock,
            image
        });

        res.status(200).json({
            message: 'Data produk berhasil diperbarui',
            data: updatedProduct
        });
    } catch (error) {
        res.status(500).json({
            message: 'Gagal memperbarui produk',
            error: error.message
        });
    }
}

// DELETE hapus produk
async function DeleteProduct(req, res) {
    try {
        const { id } = req.params;
        const destroy = await productModel.deleteProduct(id);

        if (!destroy) {
            return res.status(404).json({
                message: 'Produk atau data tidak ditemukan'
            });
        }

        res.status(200).json({
            message: 'Produk berhasil dihapus',
            data: destroy
        });
    } catch (error) {
        res.status(500).json({
            message: 'Gagal menghapus produk',
            error: error.message
        });
    }
}

module.exports = {
    index,
    GetProductById,
    CreateProduct,
    UpdateProduct,
    DeleteProduct
};