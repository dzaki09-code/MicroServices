// src/components/Navbar.jsx
import { NavLink } from 'react-router-dom'

const USERS = [1, 2, 3, 4, 5]

export default function Navbar({ userId, onUserChange, cartCount }) {
  return (
    <header className="navbar">
      <div className="navbar-left">
        <NavLink to="/" className="navbar-brand">
          ⚡ NexTech
        </NavLink>

        <nav className="navbar-links">
          <NavLink to="/" end>
            Produk
          </NavLink>

          <NavLink to="/cart">
            <span className="cart-link-wrap">
              🛒 Keranjang
              {cartCount > 0 && (
                <span className="cart-badge">{cartCount}</span>
              )}
            </span>
          </NavLink>
        </nav>
      </div>

      <label className="user">
        Akun
        <select
          value={userId}
          onChange={(e) => onUserChange(Number(e.target.value))}
        >
          {USERS.map((id) => (
            <option key={id} value={id}>
              User {id}
            </option>
          ))}
        </select>
      </label>
    </header>
  )
}
