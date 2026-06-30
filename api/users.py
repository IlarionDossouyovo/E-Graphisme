#!/usr/bin/env python3
"""
E-Graphisme Users API
Manages user registration, authentication and profiles
"""

from flask import Flask, request, jsonify
import json
import os
from datetime import datetime
import hashlib

app = Flask(__name__)

# Database path
DB_PATH = os.path.join(os.path.dirname(__file__), '..', 'db', 'users.json')

def load_users():
    """Load users from database"""
    if os.path.exists(DB_PATH):
        with open(DB_PATH, 'r') as f:
            return json.load(f)
    return []

def save_users(users):
    """Save users to database"""
    os.makedirs(os.path.dirname(DB_PATH), exist_ok=True)
    with open(DB_PATH, 'w') as f:
        json.dump(users, f, indent=2)

def hash_password(password):
    """Hash password using SHA256"""
    return hashlib.sha256(password.encode()).hexdigest()

@app.route('/api/users', methods=['GET'])
def get_users():
    """Get all users"""
    users = load_users()
    for u in users:
        u.pop('password', None)
    return jsonify({'success': True, 'users': users})

@app.route('/api/users/register', methods=['POST'])
def register():
    """Register a new user"""
    data = request.get_json()
    
    name = data.get('name')
    email = data.get('email')
    password = data.get('password')
    
    if not all([name, email, password]):
        return jsonify({'success': False, 'error': 'Missing required fields'}), 400
    
    users = load_users()
    
    # Check if email exists
    if any(u.get('email') == email for u in users):
        return jsonify({'success': False, 'error': 'Email already exists'}), 409
    
    # Create new user
    user_id = f"user_{datetime.now().strftime('%Y%m%d%H%M%S')}"
    new_user = {
        'id': user_id,
        'name': name,
        'email': email,
        'password': hash_password(password),
        'type': data.get('type', 'free'),
        'credits': 100 if data.get('type') == 'free' else 1000,
        'created': datetime.now().isoformat(),
        'updated': datetime.now().isoformat()
    }
    
    users.append(new_user)
    save_users(users)
    
    # Return user without password
    new_user.pop('password', None)
    
    return jsonify({
        'success': True,
        'message': 'User created successfully',
        'user': new_user
    })

@app.route('/api/users/login', methods=['POST'])
def login():
    """Authenticate user"""
    data = request.get_json()
    
    email = data.get('email')
    password = data.get('password')
    
    if not all([email, password]):
        return jsonify({'success': False, 'error': 'Missing credentials'}), 400
    
    users = load_users()
    password_hash = hash_password(password)
    
    # Find user
    user = next((u for u in users if u.get('email') == email and u.get('password') == password_hash), None)
    
    if not user:
        return jsonify({'success': False, 'error': 'Invalid credentials'}), 401
    
    # Return user without password
    user.pop('password', None)
    
    return jsonify({
        'success': True,
        'message': 'Login successful',
        'user': user
    })

@app.route('/api/users/<user_id>', methods=['GET'])
def get_user(user_id):
    """Get user by ID"""
    users = load_users()
    user = next((u for u in users if u.get('id') == user_id), None)
    
    if not user:
        return jsonify({'success': False, 'error': 'User not found'}), 404
    
    user.pop('password', None)
    return jsonify({'success': True, 'user': user})

@app.route('/api/users/<user_id>', methods=['PUT'])
def update_user(user_id):
    """Update user"""
    data = request.get_json()
    users = load_users()
    
    for i, u in enumerate(users):
        if u.get('id') == user_id:
            if 'password' in data:
                data['password'] = hash_password(data['password'])
            users[i].update(data)
            users[i]['updated'] = datetime.now().isoformat()
            save_users(users)
            
            users[i].pop('password', None)
            return jsonify({'success': True, 'user': users[i]})
    
    return jsonify({'success': False, 'error': 'User not found'}), 404

@app.route('/api/users/credits/<user_id>', methods=['POST'])
def update_credits(user_id):
    """Update user credits"""
    data = request.get_json()
    amount = data.get('amount', 0)
    
    users = load_users()
    
    for i, u in enumerate(users):
        if u.get('id') == user_id:
            users[i]['credits'] = users[i].get('credits', 0) + amount
            users[i]['updated'] = datetime.now().isoformat()
            save_users(users)
            
            return jsonify({
                'success': True,
                'credits': users[i]['credits']
            })
    
    return jsonify({'success': False, 'error': 'User not found'}), 404

if __name__ == '__main__':
    print("Users API running on http://localhost:8002")
    app.run(host='0.0.0.0', port=8002, debug=True)