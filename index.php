<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WEBCORD PORTAL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #000; color: #facc15; }
        .toast { animation: slideIn 0.4s ease; }
        @keyframes slideIn { from { transform: translateX(120%); opacity:0 } to { transform: translateX(0); opacity:1 } }
    </style>
</head>
<body class="min-h-screen">

<!-- AUTH SCREEN -->
<div id="auth" class="fixed inset-0 bg-black flex items-center justify-center z-50">
    <div class="bg-zinc-900 border border-yellow-600/40 rounded-3xl p-10 w-full max-w-md shadow-2xl">
        <div class="text-center mb-8">
            <div class="w-20 h-20 mx-auto bg-yellow-400 rounded-3xl flex items-center justify-center text-black text-5xl font-black shadow-lg">W</div>
            <h1 class="text-4xl font-bold mt-6">WEBCORD PORTAL</h1>
        </div>

        <div class="flex mb-6 rounded-2xl overflow-hidden">
            <button onclick="showForm('login')" class="flex-1 py-4 font-bold bg-yellow-400 text-black tab-btn active">LOGIN</button>
            <button onclick="showForm('register')" class="flex-1 py-4 font-bold bg-zinc-800 tab-btn">REGISTER</button>
        </div>

        <div id="login-form">
            <input id="l-email" type="email" placeholder="Email" class="w-full bg-zinc-800 border border-yellow-600/40 rounded-2xl px-6 py-4 mb-4 focus:outline-none focus:border-yellow-400">
            <input id="l-pass" type="password" placeholder="Password" class="w-full bg-zinc-800 border border-yellow-600/40 rounded-2xl px-6 py-4 mb-6 focus:outline-none focus:border-yellow-400">
            <button onclick="login()" class="w-full bg-yellow-400 text-black font-bold py-5 rounded-2xl hover:bg-yellow-300 transition">LOGIN</button>
        </div>

        <div id="register-form" class="hidden">
            <input id="r-email" type="email" placeholder="Email" class="w-full bg-zinc-800 border border-yellow-600/40 rounded-2xl px-6 py-4 mb-4 focus:outline-none focus:border-yellow-400">
            <input id="r-pass" type="password" placeholder="Password" class="w-full bg-zinc-800 border border-yellow-600/40 rounded-2xl px-6 py-4 mb-6 focus:outline-none focus:border-yellow-400">
            <button onclick="register()" class="w-full bg-yellow-400 text-black font-bold py-5 rounded-2xl hover:bg-yellow-300 transition">CREATE ACCOUNT (100 Credits Free)</button>
        </div>
    </div>
</div>

<!-- DASHBOARD -->
<div id="dashboard" class="hidden min-h-screen">
    <header class="bg-zinc-950 border-b border-yellow-600/30 py-5 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <div class="text-3xl font-bold tracking-tight">WEBCORD</div>
            <div class="flex items-center gap-8">
                <div id="credits" class="flex items-center gap-3 bg-zinc-900 px-6 py-3 rounded-3xl">
                    <i class="fas fa-coins text-yellow-400 text-xl"></i>
                    <span class="font-bold text-2xl">0</span>
                </div>
                <button onclick="logout()" class="text-red-400 font-medium">LOGOUT</button>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12">
        <div class="text-center mb-16">
            <h1 class="text-5xl font-bold mb-4">Welcome Back</h1>
            <p class="text-yellow-400/70 text-xl">You have <span id="big-credits" class="text-6xl font-black text-yellow-400">0</span> credits</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-zinc-900 border border-yellow-600/30 rounded-3xl p-10 text-center">
                <i class="fas fa-clock text-6xl text-yellow-400 mb-6 animate-pulse"></i>
                <h3 class="text-2xl font-bold mb-3">AFK Earning</h3>
                <p class="text-yellow-400/80 mb-6">+2 credits every 60 seconds</p>
                <div id="afk-status" class="text-sm text-green-400 font-medium">Active</div>
            </div>

            <div class="bg-zinc-900 border border-yellow-600/30 rounded-3xl p-10 text-center">
                <i class="fas fa-link text-6xl text-yellow-400 mb-6"></i>
                <h3 class="text-2xl font-bold mb-3">Webhook Sender</h3>
                <p class="text-yellow-400/80 mb-6">Cost: 90 credits</p>
                <button onclick="useTool('webhook')" class="bg-yellow-400 text-black font-bold px-10 py-4 rounded-2xl hover:bg-yellow-300 transition">USE TOOL</button>
            </div>

            <div class="bg-zinc-900 border border-yellow-600/30 rounded-3xl p-10 text-center">
                <i class="fas fa-robot text-6xl text-yellow-400 mb-6"></i>
                <h3 class="text-2xl font-bold mb-3">Bot / User Sender</h3>
                <p class="text-yellow-400/80 mb-6">Cost: 90 credits</p>
                <button onclick="useTool('bot')" class="bg-yellow-400 text-black font-bold px-10 py-4 rounded-2xl hover:bg-yellow-300 transition">USE TOOL</button>
            </div>
        </div>

        <!-- Admin Section (shown only to admin) -->
        <div id="admin-section" class="hidden mt-16 bg-zinc-900 border border-red-600/30 rounded-3xl p-10">
            <h2 class="text-3xl font-bold mb-8 text-red-400">Admin Control Panel</h2>
            <div class="mb-8">
                <input id="admin-target" placeholder="User email" class="w-full bg-zinc-800 border border-yellow-600/40 rounded-2xl px-6 py-4 mb-4">
                <input id="admin-credits" type="number" placeholder="Credits to add" class="w-full bg-zinc-800 border border-yellow-600/40 rounded-2xl px-6 py-4 mb-4">
                <button onclick="adminGiveCredits()" class="w-full bg-green-500 text-white font-bold py-5 rounded-2xl hover:bg-green-600">ADD CREDITS</button>
            </div>
            <div id="users-list" class="text-sm"></div>
        </div>
    </main>
</div>

<div id="toast-container" class="fixed bottom-8 right-8 z-50 flex flex-col gap-3 max-w-xs"></div>

<script>
const API = 'api.php?action=';

function showToast(msg, type = 'success') {
    const c = document.getElementById('toast-container');
    const t = document.createElement('div');
    t.className = `toast bg-zinc-900 border-l-4 p-4 rounded-2xl shadow-2xl flex items-center gap-3`;
    t.classList.add(type === 'success' ? 'border-green-500' : 'border-red-500');
    t.innerHTML = `<i class="fas fa-${type==='success'?'check-circle':'exclamation-triangle'} text-2xl \( {type==='success'?'text-green-400':'text-red-400'}"></i><div> \){msg}</div>`;
    c.appendChild(t);
    setTimeout(() => t.remove(), 4500);
}

function showForm(type) {
    document.getElementById('login-form').classList.toggle('hidden', type !== 'login');
    document.getElementById('register-form').classList.toggle('hidden', type !== 'register');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('bg-yellow-400', 'text-black'));
    document.querySelectorAll('.tab-btn')[type === 'login' ? 0 : 1].classList.add('bg-yellow-400', 'text-black');
}

async function register() {
    const email = document.getElementById('r-email').value.trim();
    const pass = document.getElementById('r-pass').value.trim();
    if (!email || !pass) return showToast('Email aur password daalo', 'error');

    try {
        const r = await fetch(API + 'register', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({email, password: pass})
        });
        const d = await r.json();
        if (d.token) {
            localStorage.setItem('wctoken', d.token);
            showToast('Account ban gaya! 100 credits free', 'success');
            loadDashboard();
        } else {
            showToast(d.error || 'Error', 'error');
        }
    } catch(e) {
        showToast('Network issue', 'error');
    }
}

async function login() {
    const email = document.getElementById('l-email').value.trim();
    const pass = document.getElementById('l-pass').value.trim();
    if (!email || !pass) return showToast('Email aur password daalo', 'error');

    try {
        const r = await fetch(API + 'login', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({email, password: pass})
        });
        const d = await r.json();
        if (d.token) {
            localStorage.setItem('wctoken', d.token);
            showToast('Login ho gaya!', 'success');
            loadDashboard();
        } else {
            showToast(d.error || 'Galat credentials', 'error');
        }
    } catch(e) {
        showToast('Network issue', 'error');
    }
}

async function loadDashboard() {
    const token = localStorage.getItem('wctoken');
    if (!token) return;

    try {
        const r = await fetch(API + 'me', {
            headers: { 'Authorization': 'Bearer ' + token }
        });
        if (!r.ok) throw new Error();
        const d = await r.json();

        document.getElementById('auth').classList.add('hidden');
        document.getElementById('dashboard').classList.remove('hidden');

        document.getElementById('credits').querySelector('span').innerText = d.credits;
        document.getElementById('big-credits').innerText = d.credits;

        if (d.is_admin) {
            document.getElementById('admin-section').classList.remove('hidden');
            loadAdminUsers();
        }

        startAFK();
    } catch(e) {
        localStorage.removeItem('wctoken');
        showToast('Session expired. Login again', 'error');
    }
}

function logout() {
    localStorage.removeItem('wctoken');
    location.reload();
}

let afkTimer;
function startAFK() {
    if (afkTimer) clearInterval(afkTimer);
    afkTimer = setInterval(async () => {
        const token = localStorage.getItem('wctoken');
        if (!token) return;

        try {
            const r = await fetch(API + 'earn-credit', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const d = await r.json();
            if (d.credits !== undefined) {
                document.getElementById('credits').querySelector('span').innerText = d.credits;
                document.getElementById('big-credits').innerText = d.credits;
                showToast(d.message, 'success');
            }
        } catch(e) {}
    }, 60000);
}

async function useTool(type) {
    const token = localStorage.getItem('wctoken');
    const credits = parseInt(document.getElementById('big-credits').innerText);

    if (credits < 90) {
        return showToast('90 credits chahiye tool use karne ke liye!', 'error');
    }

    showToast(`${type.toUpperCase()} tool opening... (90 credits deducted on real use)`, 'success');
    // Yahan apna purana webhook / bot sender code call kar sakte ho
    // Ya alert daal do abhi ke liye
    alert(`Tool: ${type}\nCredits deduct hone chahiye real implementation mein`);
}

async function loadAdminUsers() {
    const token = localStorage.getItem('wctoken');
    try {
        const r = await fetch(API + 'users', {
            headers: { 'Authorization': 'Bearer ' + token }
        });
        const users = await r.json();
        let html = '<h3 class="text-xl font-bold mb-4">All Users</h3><div class="grid gap-3">';
        users.forEach(u => {
            html += `<div class="bg-zinc-800 p-4 rounded-2xl flex justify-between">
                <div>
                    <div class="font-medium">${u.email}</div>
                    <div class="text-sm text-yellow-400/70">${u.credits} credits ${u.is_admin ? '(Admin)' : ''}</div>
                </div>
            </div>`;
        });
        html += '</div>';
        document.getElementById('users-list').innerHTML = html;
    } catch(e) {}
}

async function adminGiveCredits() {
    const token = localStorage.getItem('wctoken');
    const target = document.getElementById('admin-target').value.trim();
    const amount = parseInt(document.getElementById('admin-credits').value);

    if (!target || !amount || amount <= 0) {
        return showToast('User email aur valid amount daalo', 'error');
    }

    try {
        const r = await fetch(API + 'admin-give-credits', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({user: target, amount})
        });
        const d = await r.json();
        showToast(d.message || d.error, d.message ? 'success' : 'error');
        loadAdminUsers();
    } catch(e) {
        showToast('Error', 'error');
    }
}

// Init
window.onload = () => {
    if (localStorage.getItem('wctoken')) {
        loadDashboard();
    }
};
</script>
</body>
</html>
