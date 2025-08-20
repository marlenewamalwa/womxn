<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Site</title>
    <style>
        /* Header container */
        .site-header {
            background: #fff;
            padding: 15px 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        /* Search form */
        .search-form {
            display: inline-flex;
            align-items: center;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .search-input {
            padding: 10px 20px;
            border: none;
            outline: none;
            font-size: 16px;
            width: 250px;
        }

        .search-input::placeholder {
            color: #aaa;
        }

        .search-button {
            padding: 10px 20px;
            background: #d33b79;
            border: none;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-button:hover {
            background: #b42e66;
        }

        @media (max-width: 500px) {
            .search-input {
                width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="site-header">
        <form action="search.php" method="GET" class="search-form">
            <input type="text" name="q" placeholder="Search posts..." required class="search-input">
            <button type="submit" class="search-button">Search</button>
        </form>
    </div>
</body>
</html>
