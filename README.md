# DataFeedWatch Connector for Magento 2

DataFeedWatch Connector for Magento 2 module is required to allow DataFeedWatch to fetch data from your store.
The module works with **Magento Open Source** and **Adobe Commerce**.

### Module installation
1. First, log into the machine running your Magento shop via SSH.
2. Then, go to the Magento home directory.
3. Ensure the Composer is installed. To verify it, check which version you have:
    ```
   composer --version
    ```
4. Fetch DataFeedWatch Connector for Magento 2:
    ```
    composer require datafeedwatch/dfwconnector-magento2
    ```
5. Once the installation process has been completed, enable the module:
    ```
    bin/magento module:enable DataFeedWatch_Connector
    ```
6. Finally, the clean up tasks:
    ```
    bin/magento setup:upgrade
    bin/magento cache:clean
    bin/magento setup:di:compile
    ```

### Adding Magento 2 shop to DataFeedWatch

Once you’ve installed the Magento 2 plugin, refer to our guide on how to add Magento 2 shop to your account in DataFeedWatch:
https://help.datafeedwatch.com/article/783v6ihcks-adding-magento-2

