import java.util.HashMap;
import java.util.Map;
import org.junit.jupiter.api.AfterAll;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.TestInstance;
import org.openqa.selenium.By;
import org.openqa.selenium.Dimension;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.Keys;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.testng.Assert;

@TestInstance(TestInstance.Lifecycle.PER_CLASS)  // Use per-class instance lifecycle
public class loginTests {
    private WebDriver driver;
    private Map<String, Object> vars;
    private JavascriptExecutor js;

    @BeforeAll
    public void setUp() {  // No need for static now
        driver = new ChromeDriver();
        js = (JavascriptExecutor) driver;
        vars = new HashMap<>();
    }

    @AfterAll
    public void tearDown() {  // No need for static
        if (driver != null) {
            driver.quit();
        }
    }

    @Test
    public void correctLoginTest() {
        driver.get("https://vidflow.barnatech.hu/login/");
        driver.manage().window().maximize();
        driver.findElement(By.name("email")).click();
        driver.findElement(By.name("email")).sendKeys("tesztelek");
        driver.findElement(By.name("password")).click();
        driver.findElement(By.name("password")).sendKeys("test");
        driver.findElement(By.cssSelector(".login")).click();
        
        WebElement usernameDisplayed = driver.findElement(By.id("profileusername"));
        Assert.assertTrue(usernameDisplayed.isDisplayed());

    }
    
    @Test
    public void incorrectLoginTest() {
        driver.get("https://vidflow.barnatech.hu/login");
        driver.manage().window().maximize();
        driver.findElement(By.name("email")).click();
        driver.findElement(By.name("email")).sendKeys("tesztelek");
        driver.findElement(By.name("password")).click();
        driver.findElement(By.name("password")).sendKeys("testt");
        driver.findElement(By.cssSelector(".login")).click();
        
        WebElement errorMessage = driver.findElement(By.id("errors"));
        
        Assert.assertTrue(errorMessage.isDisplayed());
    }
    
    @Test
    public void logoutTest() {
        driver.get("https://vidflow.barnatech.hu/login/");
        driver.manage().window().maximize();
        driver.findElement(By.name("email")).click();
        driver.findElement(By.name("email")).sendKeys("tesztelek");
        driver.findElement(By.name("password")).sendKeys("test");
        driver.findElement(By.cssSelector(".login")).click();
        driver.findElement(By.id("logouticon")).click();
        
        WebElement loginPageDisplayed = driver.findElement(By.id("login_title"));
        
        Assert.assertTrue(loginPageDisplayed.isDisplayed());
  }
}
