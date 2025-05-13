# Commission Fee Calculator

This Symfony application calculates commission fees for financial transactions based on configurable rules. It supports both dynamic currency exchange rates via API and hardcoded rates for testing or offline usage.

---

## Requirements

- PHP 8.3 or higher
- Composer
- Symfony 6.x
- Internet connection (only if not using hardcoded rates)

---

## Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/your-username/commission-fee-calculator.git
   cd commission-fee-calculator

2. **Install dependencies**

    ```bash
   composer install

## Usage

You can run the commission calculator using a CSV file as input.

1. **Using dynamic exchange rates (default)**

    ```bash
    php bin/console app:calculate-commission input.csv


This will use exchange rates fetched from the API.
2. **Using hardcoded exchange rates**

   ```bash
   php bin/console app:calculate-commission input.csv 1

This will skip the API and use the following fixed rates:
Currency	Rate (to EUR)
EUR	1.0000
USD	1.1497
JPY	129.53


**CSV Input Format**

Each line in the CSV should follow this format:

```bash
2014-12-31,4,private,withdraw,1200.00,EUR
```

Fields:
   
    date – Transaction date (e.g., 2014-12-31)

    user_id – Integer user ID

    user_type – private or business

    operation_type – withdraw or deposit

    amount – Amount of money

    currency – Currency code (EUR, USD, JPY)

**Running Tests**

You can run the unit test suite with:

```bash
php vendor/bin/phpunit tests/CommissionAppTest.php
```

This test includes a case for hardcoded rates and validates the logic against expected commission results.
