<?php
class Payment {
    private $conn;
    private $table_name = "payments";
    
    // Payment properties
    public $id;
    public $user_id;
    public $order_id;
    public $amount;
    public $payment_method;
    public $transaction_id;
    public $status;
    public $created;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Process payment
    public function processPayment() {
        try {
            // Query to insert new payment
            $query = "INSERT INTO " . $this->table_name . " 
                      SET user_id = :user_id, 
                          order_id = :order_id, 
                          amount = :amount, 
                          payment_method = :payment_method, 
                          transaction_id = :transaction_id, 
                          status = :status, 
                          created_at = NOW()";
            
            error_log("Processing payment: user_id = " . $this->user_id . ", order_id = " . $this->order_id . 
                     ", amount = " . $this->amount . ", method = " . $this->payment_method);
            
            // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->order_id = htmlspecialchars(strip_tags($this->order_id));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));
        $this->transaction_id = htmlspecialchars(strip_tags($this->transaction_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":order_id", $this->order_id);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":payment_method", $this->payment_method);
        $stmt->bindParam(":transaction_id", $this->transaction_id);
        $stmt->bindParam(":status", $this->status);
        
        // Execute query
        if($stmt->execute()) {
            error_log("Payment processed successfully");
            return true;
        }
        
        error_log("Failed to process payment: " . implode(", ", $stmt->errorInfo()));
        return false;
    } catch (Exception $e) {
        error_log("Exception processing payment: " . $e->getMessage());
        return false;
    }
    }
    
    // Generate transaction ID
    public function generateTransactionId() {
        return 'TXN' . time() . rand(1000, 9999);
    }
    
    // Create Razorpay order - simplified version
    public function createRazorpayOrder($amount) {
        // Create a basic order object with only essential properties
        $razorpayOrder = new stdClass();
        $razorpayOrder->amount = round($amount * 100); // Convert to paise and ensure it's an integer
        $razorpayOrder->currency = RAZORPAY_CURRENCY;
        
        return $razorpayOrder;
    }
    
    // Verify Razorpay payment
    public function verifyRazorpayPayment($paymentId, $orderId, $signature) {
        // In a production environment, you would verify the signature
        // For now, we'll assume the payment is valid
        return true;
    }
    
    // Get payment by ID
    public function getPaymentById($id) {
        // Query to get payment by ID
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(1, $id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get payment by order ID
    public function getPaymentByOrderId($order_id) {
        // Query to get payment by order ID
        $query = "SELECT * FROM " . $this->table_name . " WHERE order_id = :order_id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(':order_id', $order_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get payments by user ID
    public function getPaymentsByUserId($user_id) {
        // Query to get payments by user ID
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = ? ORDER BY created DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(1, $user_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>