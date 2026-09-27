const productModel = require('../models/productModel');

// GET ambbil semua
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
        })
    }
}

async function GetProductById(req, res) {
    try {
        const { id } = req.params;
        const products = await productModel.getProductById(id);
        if (!products){
            return res.status(404).json({
                message: 'produk atau data tidak di temukan'
            });
        }
        res.status(200).json({
            message: 'berhasil mengambil data bedasarkan id',
            data: products
            
        });
    } catch (error) {
        res.status(500).json({
            message: 'gagal mengambil data bedasarkan id',
            error: error.message
        })
    }
}

async function CreateProduct(req, res) {
    try{
        const {name, price, stock} = req.body;
        if(!name || price == null){
            return res.status(404).json({
                message:'name dan price tidak boleh kosong atau wajib di isi'
            });
        }
        const newProduct = await productModel.createProducts({name, price, stock});
        res.status(201).json({
            message: 'produk berhasil di tambahkan',
            data: newProduct
        });
    }catch (error){
        res.status(500).json({
            message: 'gagal menambahkan product',
            data: error.message
        });
    }
}

async function UpdateProduct(req, res) {
    try{
        const { id } = req.params;
        const {name, price, stock}=req.body;
        const update = await productModel.updateProduct(id,{name, price, stock});
        if(!update){
            return res.status(404).json({
                message: 'Product atau data tidak di temukan'
            });
        }
        res.status(200).json({
            message: 'data atau product berhasil di update',
            data: update
        });
    }catch (error){
        res.status(500)({
            message:'gagal update product product atau data',
            data:error.message
        });
    }
}

async function DeleteProduct(req,res) {
    try{
        const { id } = req.params;
        const destroy = await productModel.deleteProduct(id);
        if (!destroy){
            return res.status(404).json({
                message: 'Product atau data tidak di temukan'
            });
        }
        res.status(200).json({
            message:'product atau Data berhasil di hapus',
            data:destroy
        });
    }catch (error){
        res.status(500).json({
            message: 'product atau data gagal di hapus',
            data:error.message
        });
    }
}

module.exports = {
    index,
    GetProductById,
    CreateProduct,
    UpdateProduct,
    DeleteProduct,
};