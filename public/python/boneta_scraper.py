from urllib.request import urlopen
from bs4 import BeautifulSoup
import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service

# Set path Selenium
CHROMEDRIVER_PATH = '/usr/local/bin/chromedriver'
s = Service(CHROMEDRIVER_PATH)
WINDOW_SIZE = "1920,1080"


# Options
chrome_options = Options()
chrome_options.add_argument("--headless")
chrome_options.add_argument("--window-size=%s" % WINDOW_SIZE)
chrome_options.add_argument('--no-sandbox')
driver = webdriver.Chrome(service=s, options=chrome_options)

# Get the response and print title
# driver.get("https://www.python.org")
# print(driver.title)

url = "https://bonetawholesale.com"
driver.get(url)
# time.sleep(5)

html = driver.page_source

# page = urlopen(url)

# html_bytes = page.read()
# html = html_bytes.decode("utf-8")

# title_index = html.find("<title>")
# start_index = title_index + len("<title>")

# end_index = html.find("</title>")
# title = html[start_index:end_index]

# print(title)
soup = BeautifulSoup(html, "html.parser")
file1 = open("boneta.txt", "w") 

def getProductList():
    html = driver.page_source
    soup = BeautifulSoup(html, "html.parser")
    results = soup.find_all("div",class_="product-list-box")
    
    for result in results:
        image = result.find("img", class_="img-fluid")
        product_name = result.find("div", class_="product-name")
        material = result.find("div",class_="materialtxt")
        box = result.find("div",class_="boxtxt")
        availability = result.find("div",class_="product-available-box")
        papers = result.find("div",class_="paperstxt")
        condition = result.find("div",class_="conditiontxt")
        description = result.find("div",class_="descriptiontxt")
        price = result.find("span",class_="pricetxt")
        print (image.get('src'))
        print(product_name.text.strip())
        print(material.text.strip())
        print(availability.text.strip())
        print(box.text.strip())
        print(papers.text.strip())
        print(condition.text.strip())
        print(description.text.strip())
        print(price.text.strip()+"/n")
        m = [product_name.text.strip()+"\n",
                image.get('src')+"\n",
                material.text.strip()+"\n",
                availability.text.strip()+"\n",
                box.text.strip()+"\n",
                papers.text.strip()+"\n",
                condition.text.strip()+"\n",
                description.text.strip()+"\n",
                price.text.strip()+"\n",
            ]
        file1.writelines (m)

def getPagination():
    ul = soup.find("ul",class_="pagination")  
    pages = ul.find_all('li')
    last_page = pages[-1]
    last_page = str(last_page.find('a',href=True))
    
    start_index = last_page.find(",'")+2
    end_index = last_page.find("')")
    last_page = last_page[start_index:end_index]

    for result in range(2, int(last_page)+1): # in ul.find_all('li'):
        # li = result.find('a',href=True)
        # if (li):
            driver.execute_script("javascript:__doPostBack('ItemList5$AspNetPager1','"+ str(result) +"')") #li.get('href'))
            getProductList()
            file1.write ("Page " + str(result) + "\n")  
            # break
    
getProductList()
getPagination()
file1.close()

driver.quit()
