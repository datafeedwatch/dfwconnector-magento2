# DataFeedWatch Connector for Magento 2

DataFeedWatch Connector for Magento 2 module is required to allow DataFeedWatch to fetch data from your store.
The module works with **Magento 2 and later** versions, and **Magento Enterprise**.

### Module installation
The installation of the Magento module can be performed by using Composer

1. You fill first need to SSH into the machine that is running your Magento shop. This will look something like this
    ```
    ssh username@123.456.79.90
    ```
    where `123.456.79.90` is an IP of your server
2. Go to Magento home directory.
3. Ensure that Composer is installed, by checking which version you have:
    ```
   composer --version
    ```
4. Inside the Magento Home directory fetch DataFeedWatch Connector for Magento 2
    ```
    composer require datafeedwatch/dfwconnector-magento2
    ```
5. Once the installation process has completed, enable the module:
    ```
    bin/magento module:enable DataFeedWatch_Connector
    ```
6. Finally clean up tasks:
    ```
    bin/magento setup:upgrade
    bin/magento cache:clean
    bin/magento setup:di:compile
    ```

### Adding Magento 2 shop to DataFeedWatch

After plugin installation is finished follow our guide to add *Magento 2* shop to your account in DataFeedWatch:
https://help.datafeedwatch.com/article/783v6ihcks-adding-magento-2

