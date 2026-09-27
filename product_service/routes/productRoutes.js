const express = require('express');
const router = express.Router();
const productController = require('../controllers/productController');


router.get ('/', productController.index);
router.get ('/:id', productController.GetProductById);
router.post ('/', productController.CreateProduct);
router.put ('/:id',productController.UpdateProduct);
router.delete('/:id',productController.DeleteProduct);

module.exports=router;