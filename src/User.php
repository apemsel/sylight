<?
namespace Sylight;

/**
 * The user of the application. Exists always in an application, even for not logged-in users.
 * Also handles login and permissions using the User DB model
 * @author Adrian Pemsel <adrian@koony.de>
 */
class User
{
  protected $app;
  
  /**
   * Constructor
   * @param Application $app Application the user is using.
   */
  public function __construct(Application $app)
  {
    // We use the full app instead of em and logger only since otherwise em would have to be created
    // for every request instead of on demand.
    $this->app = $app;
  }
  
  /**
   * Create a user in the DB. This DOES NOT login the created user!
   * @param  string $email       email
   * @param  string $password    password
   * @param  string $firstName   first name
   * @param  string $lastName    last name
   * @param  array  $permissions array of permissions in hierarchic form, e.g. ["backend.users.list", "dashboard"]
   * @return ?\Koony\Model\User  user if it has been created
   */
  public function create(string $email, string $password, string $firstName = "", string $lastName = "", array $permissions = []) : ?\Koony\Model\User
  {
    if ($this->getUserByEmail($email)) {
      return false;
    }
    
    $user = new \Koony\Model\User();
    
    $user->setEmail($email);
    $user->setPassword($password);
    if (count($permissions)) {
      $user->setPermissions($permissions);
    }
    if (!empty($firstName)) {
      $user->setFirstName($firstName);
    }
    if (!empty($lastName)) {
      $user->setLastName($lastName);
    }
    
    $this->app["em"]->persist($user);
    $this->app["em"]->flush();
    
    return $user;
  }
  
  /**
   * Login user using the given email and password
   * @param  string $email    email
   * @param  string $password password
   * @return ?\Koony\Model\User user if login succeeds
   */
  public function login(string $email, string $password) : ?\Koony\Model\User
  {
    $user = $this->getUserByEmail($email);
    
    if ($user and $user->verifyPassword($password)) {
      $session = $this->app["session"];
      $session->set("user", $user->getId());
      $session->set("username", $user->getUsername());
      $session->set("permissions", $user->getPermissions());
      
      // If the password needs to be rehashed, store the new hash
      if ($user->rehashPassword()) {
        $this->app["em"]->persist($user);
      }
      
      return $user;
    } else {
      return null;
    }
  }
  
  /**
   * Logout user. Always succeeds
   */
  public function logout()
  {
    $session = $this->app["session"];
    $session->remove("user");
    $session->remove("username");
    $session->remove("permissions");
  }

  /**
   * Check if user has the requested permission
   * @param  string $what permission string, e.g. "backend" or "backend.users.delete"
   * @return bool         true if user has permission
   */
  public function may(string $what) : bool
  {
    $permissions = $this->app["session"]->get("permissions", []);
    
    if (in_array(strtolower($what), $permissions)) { // Simple check for complete permission
      return true;
    } elseif (strrpos($what, ".") !== false) { // Recursiverly check if user has permission for one level up
      $what = substr($what, 0, strrpos($what, "."));
      return $this->may($what);
    } else { // No permission to do that
      return false;
    }
  }
  
  /**
   * Check if user is logged in
   * @return bool true if user is logged in
   */
  public function isLoggedIn() : bool
  {
    return $permissions = $this->app["session"]->has("user");
  }
  
  /**
   * Search DB user by email
   * @param  string $email      email
   * @return ?\Koony\Model\User user
   */
  public function getUserByEmail($email) : ?\Koony\Model\User
  {
    return $this->app["em"]->getRepository("\\Koony\\Model\\User")->findOneBy(["email" => $email]);
  }
  
  /**
   * Get user model of currently logged in user
   * @return ?\Koony\Model\User user
   */
  public function getUser() : ?\Koony\Model\User
  {
    if (!$this->isLoggedIn()) return null;
    
    return $this->app["em"]->find('\Koony\Model\User', $this->app["session"]->get("user"));
  }
  
  /**
   * Get user name to be shown for logged in user. Cached in session
   * so no database access is needed on every request.
   * @return string username
   */
  public function getUsername() : string
  {
    return $this->app["session"]->get("username", "");
  }
}
