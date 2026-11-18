<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class AuthMiddleware {
   public function verifyToken() {

    $headers = getallheaders();
    $token = null;

    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    }

    if (!$token) {
        Flight::halt(401, "Missing authentication token");
    }

    try {
        $decoded_token = JWT::decode(
            $token,
            new Key(Config::JWT_SECRET(), 'HS256')
        );
    } catch (Exception $e) {
        Flight::halt(401, "Invalid or expired token");
    }

    Flight::set('user', $decoded_token->user);
    Flight::set('jwt_token', $token);

    return true;
}
   public function authorizeRole($requiredRole) {
       $user = Flight::get('user');
       if ($user->role !== $requiredRole) {
           Flight::halt(403, 'Access denied: insufficient privileges');
       }
   }
   /** Allow admins, or the user whose own record is being accessed. */
   public function authorizeSelfOrAdmin($userId) {
       $user = Flight::get('user');
       if ($user->role !== Roles::ADMIN && (int) $user->id !== (int) $userId) {
           Flight::halt(403, 'Access denied: insufficient privileges');
       }
   }
   public function authorizeRoles($roles) {
       $user = Flight::get('user');
       if (!in_array($user->role, $roles)) {
           Flight::halt(403, 'Forbidden: role not allowed');
       }
   }
   
   function authorizePermission($permission) {
       $user = Flight::get('user');
       if (!in_array($permission, $user->permissions)) {
           Flight::halt(403, 'Access denied: permission missing');
       }
   }   
}