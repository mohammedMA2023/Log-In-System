<?php
class DatabaseConnection {
    private $servername;
    private $username;
    private $password;
    private $dbName;
    public $conn;

    public function __construct($servername, $username, $password, $dbName) {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbName = $dbName;
    }

    public function connect() {
        try {
            $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbName);
            echo "Database connection established successfully\n";
        } catch (mysqli_sql_exception $e) {
            echo "Error connecting to database: " . $e->getMessage() . "\n";
        }
    }

    public function close() {
        if ($this->conn) {
            $this->conn->close();
            echo "Database connection closed\n";
        }
    }
}

class UserAuthentication {
    private $db_connection;

    public function __construct($db_connection) {
        $this->db_connection = $db_connection;
    }

    public function login($email, $password) {
        try {
            session_start(); // Start the session
            $stmt = $this->db_connection->conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user) {
                $hashed_password = $user['password'];
                if (password_verify($password, $hashed_password)) {
                    // Set session variables
                    $_SESSION['userid'] = json_encode(array($user['user_id'], $user['username']));
                    $_SESSION['status'] = "loggedIn";
                    echo "Login successful\n";
                } else {
                    $_SESSION['error'] = "Error: Incorrect password";
                }
            } else {
                $_SESSION['error'] = "Error: User not found";
            }
        } catch (mysqli_sql_exception $e) {
            echo "Error during login: " . $e->getMessage() . "\n";
        }
    }

    public function register($email, $password, $username) {
        try {
            session_start(); // Start the session
            // Check password security requirements
            $error_message = $this->assess_password_security($password);
            if ($error_message) {
                $_SESSION['error'] = $error_message;
                return;
            }

            // Check if the email already exists
            $stmt = $this->db_connection->conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $existing_user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($existing_user) {
                $_SESSION['error'] = "Error: This email is already registered";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $this->db_connection->conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $hashed_password);
                $stmt->execute();
                $stmt->close();
                echo "User registered successfully\n";

                // Set session variables
                $_SESSION['userid'] = json_encode(array(mysqli_insert_id($this->db_connection->conn), $username));
                $_SESSION['status'] = "loggedIn";
            }
        } catch (mysqli_sql_exception $e) {
            echo "Error during registration: " . $e->getMessage() . "\n";
        }
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        echo "User logged out\n";
    }

    private function assess_password_security($password) {
        // Check if the password meets security requirements
        if (strlen($password) < 8) {
            return "Password must be at least 8 characters long.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return "Password must contain at least one uppercase letter.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            return "Password must contain at least one lowercase letter.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            return "Password must contain at least one digit.";
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return "Password must contain at least one special character.";
        }
        return null;
    }
}

// Example usage
$db = new DatabaseConnection("localhost", "root", "", "dash");
$db->connect();
$auth = new UserAuthentication($db);
switch ($_POST['auth']){
    case "login":
        echo "log in";
        $auth->login($_POST['userid'], $_POST['password']);
        break;
    case "reg":
        echo "reg";
        $auth->register($_POST['userid'], $_POST['password'], $_POST['uname']);
        break;
    case "logout":
        $auth->logout();
        break;
}
$db->close();
echo json_encode($_SESSION);
header("location:index.php");
exit();
?>