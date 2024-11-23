<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire Circulaire</title>
    <style>
        /* Global styles */
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
           background-image: 'ord2.jpg';
            overflow: hidden;
        }

        /* Semi-transparent circle */
        .circle-container {
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            backdrop-filter: blur(15px);
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        /* Form container */
        .form-container {
            text-align: center;
            color: #000;
            width: 80%;
        }

        .form-container h1 {
            font-size: 28px;
            margin-bottom: 20px;
            text-transform: uppercase;
            font-weight: bold;
        }

        /* Input group styling */
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            box-shadow: inset 0 1px 5px rgba(0, 0, 0, 0.1);
            outline: none;
        }

        /* Submit button styling */
        .submit-btn {
            width: 100%;
            padding: 10px;
            background: #ff6b6b;
            color: #fff;
            border: none;
            border-radius: 20px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .submit-btn:hover {
            background: #e65c5c;
        }

        /* Background effect */
        .background::before {
            content: '';
            position: absolute;
            width: 100vw;
            height: 100vh;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15), transparent);
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="background">
        <div class="circle-container">
            <form class="form-container">
                <h1>Connexion</h1>
                <div class="input-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" placeholder="Nom d'utilisateur" required />
                </div>
                <div class="input-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" placeholder="Mot de passe" required />
                </div>
                <button type="submit" class="submit-btn">Se connecter</button>
            </form>
        </div>
    </div>
</body>
</html>