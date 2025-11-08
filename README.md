# CraftHive: Fostering Community Through Local Artisan Market

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white"/>
  <img alt="MySQL" src="https://img.shields.io/badge/MySQL-%234479A1.svg?style=for-the-badge&logo=mysql&logoColor=white"/>
  <img alt="JavaScript" src="https://img.shields.io/badge/JavaScript-%23F7DF1E.svg?style=for-the-badge&logo=javascript&logoColor=black"/>
  <img alt="HTML5" src="https://img.shields.io/badge/HTML5-%23E34F26.svg?style=for-the-badge&logo=html5&logoColor=white"/>
  <img alt="CSS3" src="https://img.shields.io/badge/CSS3-%231572B6.svg?style=for-the-badge&logo=css3&logoColor=white"/>
</p>

CraftHive operates as a multi-user e-commerce ecosystem designed to connect Filipino artisans directly with customers in their local communities. The application is structured around four primary user types: **Buyer**, **Seller (Artisan)**, **Delivery Personnel**, and **Company Representative**. Each user type is provided with a distinct and highly functional portal.

The system is designed for efficient maintenance through a modular architecture, with each user role utilizing a dedicated codebase organized into separate folders. In addition to following standard web stack principles, the application integrates advanced features such as a Voice-Enabled Assistant and role-specific operational dashboards.

---

## Key Features

The platform is divided into four major portals, each with specialized features:

### 👤 Buyer Portal
- **Company and Product Discovery:** Enables browsing artisan companies and viewing their product catalogs.
- **Voice-Activated Assistant ("Craftra"):** Supports voice commands for product search and adding items to the cart.
- **Dynamic Product Views:** Displays comprehensive product details, including images, descriptions, and available customizations (size, color, shape).
- **Streamlined Checkout Process:** A multi-step process for providing shipping details and confirming orders.
- **Secure Manual Payment:** Facilitates direct, secure payments via GCash QR codes.
- **Order Tracking & Reviews:** Allows viewing order history, tracking the status of current orders, and submitting reviews for delivered products.

### 🎨 Seller (Artisan) Portal
- **Sales and Performance Dashboard:**  A dedicated interface presenting critical metrics, including total product inventory, order volume, commission earnings, and detailed revenue breakdowns.
- **Product Lifecycle Management (CRUD):** A multi-page interface for comprehensive management of product listings (creation, updating, deletion), including images, pricing, variations, stock levels, and associated shipping fees.
- **Order Fulfillment:** Provides detailed information on incoming orders and the ability to approve or reject payments.
- **Analytics and Insights:**  Visual charts displaying sales performance, top-selling products, and order status distribution.
  
### 🚚 Delivery Personnel Portal
- **Logistics Dashboard:** Displays total earnings from shipping fees and counts of delivered versus pending orders.
- **Delivery Management:** Lists pending and completed deliveries, including necessary order details and shipping addresses.
- **Proof of Delivery Upload:** A crucial feature enabling personnel to upload a photo as receipt confirmation, which updates the order status across the system.

### 🏢 Company Representative (Admin) Portal
- **Central Business Dashboard:** A high-level overview of platform performance, encompassing total registered users, subscription earnings, and commission totals.
- **User & Subscription Management:** Administration of associated sellers and delivery personnel, including account activation/deactivation and subscription plan management.
- **Financial Tracking:** Monitoring of earnings over time and recent transactions.
- **Company Profile Configuration:** Functionality to establish and update the company’s public profile, including policies and the GCash QR code for payments.

---

## 🛠️ Technology Stack

| Stack Component | Technologies Used                 |
| --------------- | ----------------------------------- |
| **Frontend**    | HTML, CSS, JavaScript, AJAX         |
| **Backend**     | PHP                                 |
| **Database**    | MySQL                               |
| **Libraries**   | PHPMailer (for password reset emails) |

---

## ⚙️ Local Installation Guide

To set up and run this project locally, execute the following steps:

1.  **Prerequisites:** Ensure XAMPP is installed and operational.

2.  **Clone the repository:**
    ```bash
    git clone https://github.com/EmanAguilera/CraftHive.git
    ```

3.  **File Placement:** Place the cloned CraftHive folder within the XAMPP htdocs directory.

4.  **Start Services:** Initiate the **Apache** and **MySQL** modules via the XAMPP Control Panel.

5.  **Database Setup:**
    - Access phpMyAdmin by clicking the **Admin** button for MySQL.
    - Create a new database named `local`.
    - Select the `local` database and navigate to the **Import** tab.
    - Select the `local(12).sql` file located in the project's `/database` folder.
    - Click **Import** to populate the necessary tables and data.

6.  **Project Execution:** Open a web browser and navigate to:
    ```
    http://localhost/CraftHive/
    ```
    The CraftHive homepage will now be accessible for system interaction.

---

## 📸 Project Gallery

<details>
<summary><strong>🏢 Company Representative & Admin Portal</strong></summary>

---
<img width="980" height="421" alt="image" src="https://github.com/user-attachments/assets/821ffca8-3315-497b-8214-1bcdf5079343" />
**Figure 1.** This dashboard displays key metrics for company representatives, including their total number, new additions, those with expiring contracts, and total earnings.

---
<img width="955" height="392" alt="image" src="https://github.com/user-attachments/assets/30b513b1-c85e-4367-8a0f-d0acb7a7982d" />
**Figure 2.** This chart displays the earnings in subscriptions for the month of December 2024.

---
<img width="980" height="420" alt="image" src="https://github.com/user-attachments/assets/77bb563b-e6c1-4134-a7d8-2c3d62a85037" />
**Figure 3.** This table lists company representatives with their subscription details and allows for filtering.

---
<img width="980" height="444" alt="image" src="https://github.com/user-attachments/assets/e6d464d9-05e5-47c6-8c50-3eb20cf376fc" />
**Figure 4.** This dashboard provides a summary of key metrics for the company, including the number of sellers, orders, affordable plan status, total commission, and earning trends.

---
<img width="980" height="449" alt="image" src="https://github.com/user-attachments/assets/63445a07-7c98-4398-9033-dd8836bfbddd" />
**Figure 5.** Displays the total commission earned for the month of January 2025.

---
<img width="980" height="443" alt="image" src="https://github.com/user-attachments/assets/cc29b1fb-aa9e-4005-afdc-31f23b296b67" />
**Figure 6.** Displays a list of recent orders with options to approve or reject each order.

---
<img width="967" height="433" alt="image" src="https://github.com/user-attachments/assets/3e4539b4-61e3-4493-9624-c0a7e18541b6" />
**Figure 7.** Page for adding a company profile, including location, payment details (GCash QR), and policy.

---
<img width="980" height="444" alt="image" src="https://github.com/user-attachments/assets/338e8427-caed-4424-9129-b68362ca45e2" />
**Figure 8.** The company profile display page, with options to update or erase the information.

---
<img width="980" height="441" alt="image" src="https://github.com/user-attachments/assets/19d8ba9b-be1d-4b4d-9a68-20529701f64f" />
**Figure 9.** User management page to activate or deactivate sellers and delivery persons.

---
<img width="980" height="439" alt="image" src="https://github.com/user-attachments/assets/e33ee83c-ef3b-478c-ac46-177dda557a63" />
**Figure 10.** Table displaying a list of all products from sellers managed by the company.

---
<img width="952" height="418" alt="image" src="https://github.com/user-attachments/assets/a767bb56-7628-4556-a9e8-370bb7de4ae2" />
**Figure 11.** Subscription plan page with QR code for payment.

---
<img width="941" height="407" alt="image" src="https://github.com/user-attachments/assets/4868a16b-0250-4046-a362-610b0c96eaed" />
**Figure 12.** Page for editing the company profile.

</details>

<details>
<summary><strong>🎨 Seller (Artisan) Portal</strong></summary>

---
<img width="980" height="445" alt="image" src="https://github.com/user-attachments/assets/443e18f5-8a84-4747-aa17-99896013d586" />
**Figure 13.** The seller dashboard with key metrics: total products, orders, average rating, and total commission.

---
<img width="980" height="439" alt="image" src="https://github.com/user-attachments/assets/c7ef0a4b-ea28-4bf0-9015-229720a9baaa" />
**Figure 14.** A chart displaying the seller's commission breakdown for different products.

---
<img width="980" height="444" alt="image" src="https://github.com/user-attachments/assets/c3f2eac9-9cc1-4354-9036-b14629a5da03" />
**Figure 15.** A breakdown of product revenue, showing the seller's and company's shares.

---
<img width="980" height="444" alt="image" src="https://github.com/user-attachments/assets/403b3a80-9792-4adb-9a1e-06ec18fe5da9" />
**Figure 16.** A list of recent orders with options to approve or reject.

---
<img width="980" height="442" alt="image" src="https://github.com/user-attachments/assets/fa78582c-40cc-4883-ba99-b83fec697eea" />
**Figure 17.** The first step of the multi-page form for adding a new product.

---
<img width="980" height="441" alt="image" src="https://github.com/user-attachments/assets/b5c63423-c910-408f-bc87-8d7346a703e3" />
**Figure 18.** The second step for adding product details, including variations, stock, and shipping.

---
<img width="980" height="444" alt="image" src="https://github.com/user-attachments/assets/7fe0bb3f-f6d9-4e0c-9ba0-ea0adc1a4ee5" />
**Figure 19.** The final step for adding a product, including assigning a delivery person.

---
<img width="980" height="443" alt="image" src="https://github.com/user-attachments/assets/2d5a80e6-ee55-46d9-93c1-3e808a4f7718" />
**Figure 20.** A list of the seller's own products with options to update or erase them.

---
<img width="980" height="441" alt="image" src="https://github.com/user-attachments/assets/8a0d6958-536c-4114-94fb-d036f5766cb7" />
**Figure 21.** The first page for editing an existing product's details.

---
<img width="980" height="443" alt="image" src="https://github.com/user-attachments/assets/31a8d44b-91ca-4211-9ec0-32181d4df965" />
**Figure 22.** The second page for editing product details.

---
<img width="980" height="444" alt="image" src="https://github.com/user-attachments/assets/8477ae92-1a96-4e63-b94d-d040913a954d" />
**Figure 23.** A pie chart showing the distribution of order statuses (pending, delivered, approved).

---
<img width="980" height="444" alt="image" src="https://github.com/user-attachments/assets/b66ca073-b060-4a90-b513-1873104bcccc" />
**Figure 24.** A list of pending orders awaiting approval.

---
<img width="980" height="442" alt="image" src="https://github.com/user-attachments/assets/8376bd6a-625f-4077-82a4-a792b4782fba" />
**Figure 25.** A bar chart displaying the top 5 best-selling products.

---
<img width="980" height="441" alt="image" src="https://github.com/user-attachments/assets/f47d1ff4-3227-44e6-943d-ea2bece8c3c7" />
**Figure 26.** A list of products with their average ratings and review counts, with an option to view individual reviews.

</details>

<details>
<summary><strong>🚚 Delivery Personnel Portal</strong></summary>

---
<img width="937" height="427" alt="image" src="https://github.com/user-attachments/assets/d6f7e44e-d917-43a1-9ff4-4b161feb6a55" />
**Figure 27.** The delivery dashboard with key metrics: total shipping fee, delivered orders, pending orders, and total revenue.

---
<img width="929" height="421" alt="image" src="https://github.com/user-attachments/assets/ef2accd9-82f5-46ee-a9aa-596a7ae4074b" />
**Figure 28.** A chart displaying the delivery share for different products.

---
<img width="980" height="445" alt="image" src="https://github.com/user-attachments/assets/24aa7ce5-5d87-47af-bb98-32f8a7563699" />
**Figure 29.** Lists of products with their total revenue and the corresponding delivery share.

---
<img width="980" height="445" alt="image" src="https://github.com/user-attachments/assets/d750e757-45fb-48fb-b17f-8ae7329d87e7" />
**Figure 30.** A table displaying a list of recent orders assigned for delivery.

---
<img width="980" height="442" alt="image" src="https://github.com/user-attachments/assets/c35033ed-f507-4ccb-9ab3-5bcea31955d4" />
**Figure 31.** A list of pending delivery receipts that need a proof of delivery to be uploaded.

---
<img width="980" height="447" alt="image" src="https://github.com/user-attachments/assets/7e8fab59-3160-4603-984d-8c9a54fb1a63" />
**Figure 32.** The page for uploading a proof of receipt for a delivered order.

---
<img width="980" height="451" alt="image" src="https://github.com/user-attachments/assets/6af6274e-ac47-4c5a-be19-ce3fc0597e3c" />
**Figure 33.** An example of a proof of receipt from a delivery company.

---
<img width="980" height="440" alt="image" src="https://github.com/user-attachments/assets/3fd32d66-182a-4cc1-aa28-6dba7713d49a" />
**Figure 34.** A list of successfully delivered products with their proof of receipt.

</details>

<details>
<summary><strong>👤 Buyer (Customer) Portal & Shopping Experience</strong></summary>

---
<img width="1853" height="910" alt="image" src="https://github.com/user-attachments/assets/304c1135-61e6-419d-b0e3-395e22bc17cb" />
**Figure 35.** The main landing page introducing CraftHive and its products.

---
<img width="980" height="437" alt="image" src="https://github.com/user-attachments/assets/54c02460-78c3-4910-b179-b0093b55effd" />
**Figure 36.** The company directory page, allowing users to select an artisan company to browse.

---
<img width="980" height="443" alt="image" src="https://github.com/user-attachments/assets/66fe93ed-f12d-40e8-9061-e1fb143d879d" />
**Figure 37.** A specific company's product listing page.

---
<img width="980" height="440" alt="image" src="https://github.com/user-attachments/assets/91b8895f-ecd6-4817-8b42-480b6360d0ee" />
**Figure 38.** The product details pop-up modal with customization options.

---
<img width="980" height="421" alt="image" src="https://github.com/user-attachments/assets/68c7f95a-f24f-434a-b600-77ed009d2236" />
**Figure 39.** The introduction to "Craftra," the voice assistant.

---
<img width="980" height="423" alt="image" src="https://github.com/user-attachments/assets/96ecd55c-9d02-4a23-9f7f-436f81b05d2f" />
**Figure 40.** The voice assistant showing search results for "lightning" products.

---
<img width="980" height="421" alt="image" src="https://github.com/user-attachments/assets/0777fd95-cc11-406a-8abb-1826304031ed" />
**Figure 41.** The voice assistant confirming an order to be added to the cart.

---
<img width="980" height="418" alt="image" src="https://github.com/user-attachments/assets/c4f68bfa-c0b8-4226-8e9b-f57a0b7a9b14" />
**Figure 42.** The shopping cart page, showing all contents and totals.

---
<img width="980" height="420" alt="image" src="https://github.com/user-attachments/assets/b6b7a658-2471-4aae-8d0f-a28888276295" />
**Figure 43.** The first step of the checkout process: order summary and shipping details.

---
<img width="980" height="419" alt="image" src="https://github.com/user-attachments/assets/5765e941-eea3-4ba3-b0d4-a8fb8d33cb5b" />
**Figure 44.** The payment page showing the GCash QR code and reference number.

---
<img width="980" height="423" alt="image" src="https://github.com/user-attachments/assets/031e864f-2fbf-48ca-95f9-4ff8149ea008" />
**Figure 45.** The order success confirmation pop-up.

---
<img width="980" height="423" alt="image" src="https://github.com/user-attachments/assets/7365d0fe-9a22-4825-b4a1-77fbb1ccc3db" />
**Figure 46.** The "empty cart" notification pop-up.

---
<img width="980" height="421" alt="image" src="https://github.com/user-attachments/assets/60f067f7-09bf-415b-98b3-f7d65c7dd409" />
**Figure 47.** An example of a populated cart.

---
<img width="980" height="421" alt="image" src="https://github.com/user-attachments/assets/84cb4cc7-e088-4fd4-85c9-a3867edc85de" />
**Figure 48.** An example of the checkout page with an item summary.

---
<img width="980" height="386" alt="image" src="https://github.com/user-attachments/assets/b426ac7a-9d13-49f3-947b-657208a38ae3" />
**Figure 49.** The customer's order history page with an option to add a review.

---
<img width="980" height="421" alt="image" src="https://github.com/user-attachments/assets/e27434ee-d8bf-4dfc-b4f6-fb4e822ede9a" />
**Figure 50.** The page for writing and submitting a product review.

</details>

<details>
<summary><strong>🔑 User Authentication (Registration, Login, Password Reset)</strong></summary>

---
<img width="980" height="419" alt="image" src="https://github.com/user-attachments/assets/9ffd6deb-64ec-421a-af39-811572521c23" />
**Figure 51.** The main registration page for buyers.

---
<img width="980" height="421" alt="image" src="https://github.com/user-attachments/assets/df81a01f-662e-4360-8498-f2a1b320c4d2" />
**Figure 52.** The "Forgot Password" page for sellers.

---
<img width="980" height="420" alt="image" src="https://github.com/user-attachments/assets/fc1de5ec-bc35-42be-90b0-fbc33ad568c3" />
**Figure 53.** The password reset page using a verification code from email.

---
<img width="980" height="418" alt="image" src="https://github.com/user-attachments/assets/e2699091-c8f6-49cf-a235-b7669e207fdd" />
**Figure 54.** The login page for sellers.

---
<img width="980" height="418" alt="image" src="https://github.com/user-attachments/assets/8ce5b607-cd38-4921-a0a8-7c326992e64d" />
**Figure 55.** The registration page for company representatives.

---
<img width="980" height="421" alt="image" src="https://github.com/user-attachments/assets/38accbe9-1157-4f35-8e69-e1e7b4e6ef61" />
**Figure 56.** The login page for company representatives.

---
<img width="980" height="419" alt="image" src="https://github.com/user-attachments/assets/f502b5f5-c9c4-42d3-9eb8-7eef7c07b7e0" />
**Figure 57.** The One-Time Password (OTP) verification page for company representatives.

---
<img width="980" height="420" alt="image" src="https://github.com/user-attachments/assets/7beb2238-f408-4e44-8357-4b32f33ac254" />
**Figure 58.** The password reset page for company representatives.

---
<img width="980" height="417" alt="image" src="https://github.com/user-attachments/assets/1eb971f1-027e-4571-9cf6-6850cee7a712" />
**Figure 59.** The registration page for delivery persons.

---
<img width="980" height="420" alt="image" src="https://github.com/user-attachments/assets/1aec7759-ee2d-48dd-8382-2f81ade38b8f" />
**Figure 60.** The login page for delivery persons.

---
<img width="980" height="421" alt="image" src="https://github.com/user-attachments/assets/3aa2d955-5786-42dd-8291-ecf28d6d3e15" />
**Figure 61.** The OTP verification page for delivery persons.

---
<img width="980" height="419" alt="image" src="https://github.com/user-attachments/assets/25cefc30-d3e1-40ae-a91c-2617cee43e77" />
**Figure 62.** The "Forgot Password" page for delivery persons.

---
<img width="980" height="421" alt="image" src="https://github.com/user-attachments/assets/401bd55e-854d-41b7-b525-5ac12df0621f" />
**Figure 63.** The password reset page for delivery persons.

</details>
