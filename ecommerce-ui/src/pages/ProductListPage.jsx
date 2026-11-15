// src/pages/ProductListPage.jsx
import { useState } from 'react'
import { getProductsWithRating } from '../api/aggregate'
import AsyncView from '../components/AsyncView'
import ProductCard from '../components/ProductCard'
import { useAsync } from '../hooks/useAsync'

export default function ProductListPage() {
  const { loading, data, error, reload } = useAsync(getProductsWithRating)
  const [query, setQuery] = useState('')
  const normalizedQuery = query.trim().toLowerCase()
  const filteredProducts = data?.filter((product) =>
    `${product.name} ${product.description ?? ''}`
      .toLowerCase()
      .includes(normalizedQuery)
  )

  return (
    <>
      {/* Hero banner */}
      <div className="hero">
        <div className="hero-content">
          <h1>
            Temukan Gadget<br />Impianmu
          </h1>
          <p>Laptop, HP, Aksesoris — semua ada di GadgetHub</p>
        </div>
      </div>

      {/* Product list */}
      <section>
        <h2 className="section-heading">Semua Produk</h2>
        <div className="product-search">
          <label htmlFor="product-search-input">Cari produk</label>
          <input
            id="product-search-input"
            type="search"
            value={query}
            onChange={(event) => setQuery(event.target.value)}
            placeholder="Cari nama atau deskripsi produk..."
          />
        </div>

        <AsyncView loading={loading} error={error} onRetry={reload}>
          {data && (
            filteredProducts.length > 0 ? (
              <div className="grid">
                {filteredProducts.map((product) => (
                  <ProductCard key={product.id} product={product} />
                ))}
              </div>
            ) : (
              <p className="product-search-empty" role="status">
                {normalizedQuery
                  ? `Tidak ada produk yang cocok dengan "${query.trim()}".`
                  : 'Belum ada produk.'}
              </p>
            )
          )}
        </AsyncView>
      </section>
    </>
  )
}
