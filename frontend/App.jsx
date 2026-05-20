import React, { useState, useEffect } from 'react';

function App() {
    const [loggedIn, setLoggedIn] = useState(false);
    const [currentUser, setCurrentUser] = useState(null);
    const [isRegistering, setIsRegistering] = useState(false);
    const [loginUsername, setLoginUsername] = useState('');
    const [loginPassword, setLoginPassword] = useState('');
    const [regUsername, setRegUsername] = useState('');
    const [regPassword, setRegPassword] = useState('');
    const [regRole, setRegRole] = useState('user');

    const [products, setProducts] = useState([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [auditLogs, setAuditLogs] = useState([]);
    const [editingProduct, setEditingProduct] = useState(null);
    const [formData, setFormData] = useState({ name: '', category: '', price: '', stock: '', supplier: '' });
    const [transactionMessage, setTransactionMessage] = useState('');
    const [users, setUsers] = useState(() => {
        const saved = localStorage.getItem('stockly_users');
        if (saved) return JSON.parse(saved);
        return [{ username: 'admin', password: 'admin', role: 'admin' }];
    });

    const addAuditLog = (action, details) => {
        const newLog = { id: Date.now(), timestamp: new Date().toLocaleString(), action, details };
        setAuditLogs(prev => [newLog, ...prev].slice(0, 20));
    };

    useEffect(() => {
        localStorage.setItem('stockly_users', JSON.stringify(users));
    }, [users]);

    const handleRegister = (e) => {
        e.preventDefault();
        if (!regUsername || !regPassword) return alert('Fill all fields');
        if (users.find(u => u.username === regUsername)) return alert('Username exists');
        const newUser = { username: regUsername, password: regPassword, role: regRole };
        setUsers([...users, newUser]);
        addAuditLog('REGISTER', `New user: ${regUsername} (${regRole})`);
        alert('Registration successful! Please login.');
        setIsRegistering(false);
        setRegUsername('');
        setRegPassword('');
        setRegRole('user');
    };

    const handleLogin = (e) => {
        e.preventDefault();
        const user = users.find(u => u.username === loginUsername && u.password === loginPassword);
        if (user) {
            setLoggedIn(true);
            setCurrentUser(user);
            addAuditLog('LOGIN', `${user.username} (${user.role}) logged in`);
            fetchProducts();
        } else {
            alert('Invalid credentials');
        }
    };

    const handleLogout = () => {
        addAuditLog('LOGOUT', `${currentUser?.username} logged out`);
        setLoggedIn(false);
        setCurrentUser(null);
        setLoginUsername('');
        setLoginPassword('');
    };

    const API_BASE = 'http://localhost/YourGroupName-2a/backend/api';

    const fetchProducts = async () => {
        try {
            const res = await fetch(`${API_BASE}/products.php`);
            const data = await res.json();
            if (!data.error) setProducts(data);
            else console.error(data.error);
        } catch (error) {
            console.error("Fetch error:", error);
        }
    };

    const addProduct = async (e) => {
        e.preventDefault();
        try {
            const res = await fetch(`${API_BASE}/add_product.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData),
            });
            const result = await res.json();
            if (result.success) {
                fetchProducts();
                addAuditLog('INSERT', `Added product: ${formData.name}`);
                setFormData({ name: '', category: '', price: '', stock: '', supplier: '' });
                alert('Product added');
            } else {
                alert('Failed');
            }
        } catch (error) {
            console.error(error);
        }
    };

    const updateProduct = async (e) => {
        e.preventDefault();
        try {
            const res = await fetch(`${API_BASE}/update_product.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...formData, id: editingProduct.id }),
            });
            const result = await res.json();
            if (result.success) {
                fetchProducts();
                addAuditLog('UPDATE', `Updated product: ${formData.name}`);
                setEditingProduct(null);
                setFormData({ name: '', category: '', price: '', stock: '', supplier: '' });
                alert('Product updated');
            } else {
                alert('Failed');
            }
        } catch (error) {
            console.error(error);
        }
    };

    const deleteProduct = async (id, name) => {
        if (!window.confirm(`Delete ${name}?`)) return;
        try {
            const res = await fetch(`${API_BASE}/delete_product.php`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id }),
            });
            const result = await res.json();
            if (result.success) {
                fetchProducts();
                addAuditLog('DELETE', `Deleted product: ${name}`);
                alert('Product deleted');
            } else {
                alert('Failed');
            }
        } catch (error) {
            console.error(error);
        }
    };

    const sellProduct = (id, name, currentStock) => {
        if (currentStock <= 0) {
            setTransactionMessage(`Cannot sell ${name}: out of stock`);
            return;
        }
        const newStock = currentStock - 1;
        setProducts(products.map(p => p.id === id ? { ...p, stock: newStock } : p));
        addAuditLog('TRANSACTION', `Sold 1 of ${name}. New stock: ${newStock}`);
        setTransactionMessage(`Sold 1 ${name}`);
        setTimeout(() => setTransactionMessage(''), 3000);
    };

    const startEdit = (product) => {
        setEditingProduct(product);
        setFormData({
            name: product.name,
            category: product.category || '',
            price: product.price,
            stock: product.stock,
            supplier: product.supplier,
        });
    };

    const filteredProducts = products.filter(p =>
        p.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (p.supplier && p.supplier.toLowerCase().includes(searchTerm.toLowerCase()))
    );

    if (!loggedIn) {
        return (
            <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
                <div className="bg-white rounded-2xl shadow-xl max-w-md w-full p-8">
                    <h1 className="text-3xl font-bold text-center mb-2">Stockly</h1>
                    <p className="text-center text-gray-500 mb-6">Inventory System</p>
                    {!isRegistering ? (
                        <form onSubmit={handleLogin} className="space-y-5">
                            <div><label className="block text-sm font-medium">Username</label><input type="text" value={loginUsername} onChange={e => setLoginUsername(e.target.value)} className="mt-1 w-full border rounded-lg px-3 py-2" required /></div>
                            <div><label className="block text-sm font-medium">Password</label><input type="password" value={loginPassword} onChange={e => setLoginPassword(e.target.value)} className="mt-1 w-full border rounded-lg px-3 py-2" required /></div>
                            <button type="submit" className="w-full bg-blue-600 text-white py-2 rounded-lg">Login</button>
                            <p className="text-center text-sm">No account? <button type="button" onClick={() => setIsRegistering(true)} className="text-blue-600">Register</button></p>
                        </form>
                    ) : (
                        <form onSubmit={handleRegister} className="space-y-5">
                            <div><label className="block text-sm font-medium">Username</label><input type="text" value={regUsername} onChange={e => setRegUsername(e.target.value)} className="mt-1 w-full border rounded-lg px-3 py-2" required /></div>
                            <div><label className="block text-sm font-medium">Password</label><input type="password" value={regPassword} onChange={e => setRegPassword(e.target.value)} className="mt-1 w-full border rounded-lg px-3 py-2" required /></div>
                            <div><label className="block text-sm font-medium">Role</label><select value={regRole} onChange={e => setRegRole(e.target.value)} className="mt-1 w-full border rounded-lg px-3 py-2"><option value="user">User</option><option value="admin">Admin</option></select></div>
                            <button type="submit" className="w-full bg-green-600 text-white py-2 rounded-lg">Register</button>
                            <p className="text-center text-sm">Already have an account? <button type="button" onClick={() => setIsRegistering(false)} className="text-blue-600">Login</button></p>
                        </form>
                    )}
                </div>
            </div>
        );
    }

    // Admin Dashboard
    if (currentUser?.role === 'admin') {
        return (
            <div className="min-h-screen bg-gray-50">
                <div className="bg-white shadow-sm border-b px-4 py-4 flex justify-between items-center">
                    <div><h1 className="text-2xl font-bold">Stockly Admin</h1><p className="text-sm text-gray-500">{currentUser.username} ({currentUser.role})</p></div>
                    <button onClick={handleLogout} className="bg-red-100 text-red-700 px-4 py-2 rounded-lg">Logout</button>
                </div>
                <div className="max-w-7xl mx-auto px-4 py-6">
                    {transactionMessage && <div className="mb-4 p-3 bg-green-100 text-green-800 rounded-lg">{transactionMessage}</div>}
                    <div className="grid lg:grid-cols-3 gap-6">
                        <div className="space-y-6">
                            <div className="bg-white rounded-xl shadow-sm p-5">
                                <h2 className="text-lg font-semibold mb-4">{editingProduct ? 'Update Product' : 'Add Product'}</h2>
                                <form onSubmit={editingProduct ? updateProduct : addProduct} className="space-y-3">
                                    <input type="text" placeholder="Name" value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} className="w-full border rounded-lg px-3 py-2" required />
                                    <input type="text" placeholder="Category" value={formData.category} onChange={e => setFormData({...formData, category: e.target.value})} className="w-full border rounded-lg px-3 py-2" />
                                    <input type="number" step="0.01" placeholder="Price" value={formData.price} onChange={e => setFormData({...formData, price: e.target.value})} className="w-full border rounded-lg px-3 py-2" required />
                                    <input type="number" placeholder="Stock" value={formData.stock} onChange={e => setFormData({...formData, stock: e.target.value})} className="w-full border rounded-lg px-3 py-2" required />
                                    <input type="text" placeholder="Supplier" value={formData.supplier} onChange={e => setFormData({...formData, supplier: e.target.value})} className="w-full border rounded-lg px-3 py-2" />
                                    <div className="flex gap-2">
                                        <button type="submit" className="flex-1 bg-blue-600 text-white py-2 rounded-lg">{editingProduct ? 'Update' : 'Add'}</button>
                                        {editingProduct && <button type="button" onClick={() => { setEditingProduct(null); setFormData({ name: '', category: '', price: '', stock: '', supplier: '' }); }} className="bg-gray-300 px-4 rounded-lg">Cancel</button>}
                                    </div>
                                </form>
                            </div>
                            <div className="bg-white rounded-xl shadow-sm p-5">
                                <h2 className="text-lg font-semibold mb-3">Audit Log</h2>
                                <div className="max-h-64 overflow-y-auto text-sm space-y-2">
                                    {auditLogs.map(log => <div key={log.id} className="border-l-4 border-blue-400 pl-2"><span className="text-xs text-gray-500">{log.timestamp}</span><p><span className="font-medium">{log.action}</span>: {log.details}</p></div>)}
                                </div>
                            </div>
                        </div>
                        <div className="lg:col-span-2 space-y-6">
                            <div className="bg-white rounded-xl shadow-sm p-5"><input type="text" placeholder="Search products..." value={searchTerm} onChange={e => setSearchTerm(e.target.value)} className="w-full border rounded-lg px-3 py-2" /></div>
                            <div className="bg-white rounded-xl shadow-sm overflow-x-auto">
                                <table className="min-w-full text-sm">
                                    <thead className="bg-gray-50"><tr><th className="p-3 text-left">Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Supplier</th><th className="text-center">Actions</th></tr></thead>
                                    <tbody>{filteredProducts.map(p => <tr key={p.id} className="border-t"><td className="p-3">{p.name}</td><td className="p-3">{p.category || 'General'}</td><td className="p-3">${p.price}</td><td className="p-3">{p.stock}</td><td className="p-3">{p.supplier}</td><td className="p-3 text-center space-x-2"><button onClick={() => sellProduct(p.id, p.name, p.stock)} className="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Sell</button><button onClick={() => startEdit(p)} className="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">Edit</button><button onClick={() => deleteProduct(p.id, p.name)} className="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Delete</button></td></tr>)}</tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    // User Dashboard
    return (
        <div className="min-h-screen bg-gray-50">
            <div className="bg-white shadow-sm border-b px-4 py-4 flex justify-between items-center">
                <div><h1 className="text-2xl font-bold">Stockly User</h1><p className="text-sm text-gray-500">{currentUser.username} ({currentUser.role})</p></div>
                <button onClick={handleLogout} className="bg-red-100 text-red-700 px-4 py-2 rounded-lg">Logout</button>
            </div>
            <div className="max-w-7xl mx-auto px-4 py-6">
                {transactionMessage && <div className="mb-4 p-3 bg-green-100 text-green-800 rounded-lg">{transactionMessage}</div>}
                <div className="space-y-6">
                    <div className="bg-white rounded-xl shadow-sm p-5"><input type="text" placeholder="Search..." value={searchTerm} onChange={e => setSearchTerm(e.target.value)} className="w-full border rounded-lg px-3 py-2" /></div>
                    <div className="bg-white rounded-xl shadow-sm overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead className="bg-gray-50"><tr><th className="p-3 text-left">Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Supplier</th><th className="text-center">Action</th></tr></thead>
                            <tbody>{filteredProducts.map(p => <tr key={p.id} className="border-t"><td className="p-3">{p.name}</td><td className="p-3">{p.category || 'General'}</td><td className="p-3">${p.price}</td><td className="p-3">{p.stock}</td><td className="p-3">{p.supplier}</td><td className="p-3 text-center"><button onClick={() => sellProduct(p.id, p.name, p.stock)} className="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Buy</button></td></tr>)}</tbody>
                        </table>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-5">
                        <h2 className="text-lg font-semibold mb-3">Audit Log</h2>
                        <div className="max-h-64 overflow-y-auto text-sm space-y-2">
                            {auditLogs.map(log => <div key={log.id} className="border-l-4 border-blue-400 pl-2"><span className="text-xs text-gray-500">{log.timestamp}</span><p><span className="font-medium">{log.action}</span>: {log.details}</p></div>)}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default App;