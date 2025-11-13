<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRAND CHASE </title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh; color: #333;
        }
        .container {
            max-width: 500px; margin: 50px auto;
            background: white; border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden;
        }
        .header {
            background: #2c3e50; color: white;
            padding: 30px; text-align: center;
        }
        .header h1 { font-size: 2.2em; margin-bottom: 10px; }
        .content { padding: 40px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0;
            border-radius: 8px; font-size: 16px; transition: border-color 0.3s ease;
        }
        input:focus { outline: none; border-color: #3498db; }
        .btn {
            width: 100%; background: #3498db; color: white; padding: 14px;
            border: none; border-radius: 8px; cursor: pointer;
            font-size: 16px; font-weight: 600; transition: all 0.3s ease;
        }
        .btn:hover { background: #2980b9; transform: translateY(-2px); }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #219a52; }
        .message {
            padding: 15px; border-radius: 8px; margin-bottom: 20px;
            text-align: center; font-weight: 600;
        }
        .message.success { background: #d5f4e6; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #fadbd8; color: #721c24; border: 1px solid #f5c6cb; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #3498db; text-decoration: none; margin: 0 10px; font-weight: 600; }
        .links a:hover { text-decoration: underline; }
        .user-info { background: #ecf0f1; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 GRAND CHASE</h1>
            <div class="subtitle">DOWNLOAD, LOGIN E REGISTRO</div>
        </div>
        <div class="content">