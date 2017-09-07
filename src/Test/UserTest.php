<?
use Sylight\User;

class UserTest extends FrameworkTestCase
{
  public function setUp()
  {
    parent::setUp();
    $this->deleteTestUser();
  }
  
  public function tearDown()
  {
    $this->deleteTestUser();
    parent::tearDown();
  }
  
  protected function deleteTestUser()
  {
    $testUser = $this->app["em"]->getRepository("\\Koony\\Model\\User")->findOneBy(["email" => "testuser@koony.de"]);
    if ($testUser) {
      $this->app["em"]->remove($testUser);
      $this->app["em"]->flush();
    }
  }
  
  public function testCreate()
  {
    $appUser = $this->app["user"];
    $dbUser = $appUser->create("testuser@koony.de", "password", "Max", "Muster");
    $this->assertInstanceOf(\Koony\Model\User::class, $dbUser);
  }

  public function testLogin()
  {
    $appUser = $this->app["user"];
    $dbUser = $appUser->create("testuser@koony.de", "password", "Max", "Muster");
    $user = $appUser->login("testuser@koony.de", "password");
    $this->assertInstanceOf(\Koony\Model\User::class, $user);
    $user = $appUser->login("testuser@koony.de", "notthepassword");
    $this->assertEquals(false, $user);
    $user = $appUser->login("unknown@koony.de", "password");
    $this->assertEquals(false, $user);
  }
  
  public function testisLoggedIn()
  {
    $appUser = $this->app["user"];
    $dbUser = $appUser->create("testuser@koony.de", "password", "Max", "Muster");
    $this->assertEquals(false, $appUser->isLoggedIn());
    $isLoggedIn = $appUser->login("testuser@koony.de", "password");
    $this->assertEquals(true, $appUser->isLoggedIn());
  }
  
  public function testLogout()
  {
    $appUser = $this->app["user"];
    $dbUser = $appUser->create("testuser@koony.de", "password", "Max", "Muster");
    $isLoggedIn = $appUser->login("testuser@koony.de", "password");
    $this->assertEquals(true, $appUser->isLoggedIn());
    $appUser->logout();
    $this->assertEquals(false, $appUser->isLoggedIn());
  }
  
  public function testGetUserByEmail()
  {
    $appUser = $this->app["user"];
    $dbUser = $appUser->create("testuser@koony.de", "password", "Max", "Muster");
    $dbUser = $appUser->getUserByEmail("testuser@koony.de");
    $this->assertInstanceOf(\Koony\Model\User::class, $dbUser);
  }
  
  public function testGetUser()
  {
    $appUser = $this->app["user"];
    $appUser->create("testuser@koony.de", "password", "Max", "Muster");
    $isLoggedIn = $appUser->login("testuser@koony.de", "password");
    $this->assertEquals(true, $appUser->isLoggedIn());
    $this->assertInstanceOf(\Koony\Model\User::class, $appUser->getUser());
    $appUser->logout();
    $this->assertEquals(null, $appUser->getUser());
  }
  
  public function testMay()
  {
    $appUser = $this->app["user"];
    $dbUser = $appUser->create("testuser@koony.de", "password", "Max", "Muster", ["backend.users.list", "backend.airports.create", "dashboard"]);
    $appUser->login("testuser@koony.de", "password");
    $this->assertEquals(true, $appUser->may("backend.airports.create"));
    $this->assertEquals(false, $appUser->may("backend.users.delete"));
    $this->assertEquals(true, $appUser->may("dashboard.revenue.show"));
  }
  
  public function testGetUsername()
  {
    $appUser = $this->app["user"];
    $dbUser = $appUser->create("testuser@koony.de", "password", "Max", "Muster");
    $appUser->login("testuser@koony.de", "password");
    $this->assertEquals("Max Muster", $appUser->getUsername());
  }

}
