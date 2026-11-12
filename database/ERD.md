# UIBite — Entity Relationship Diagram

```mermaid
erDiagram
    USERS ||--o| STORES : owns
    USERS ||--o{ ORDERS : places
    USERS ||--o{ REVIEWS : writes
    STORES ||--o{ PRODUCTS : offers
    STORES ||--o{ ORDERS : receives
    STORES ||--o{ STOCK_MOVEMENTS : records
    CATEGORIES ||--o{ PRODUCTS : classifies
    ORDERS ||--|{ ORDER_ITEMS : contains
    ORDERS ||--|| PAYMENTS : has
    PRODUCTS ||--o{ ORDER_ITEMS : purchased_as
    PRODUCTS ||--o{ STOCK_MOVEMENTS : tracks
    ORDERS ||--o{ STOCK_MOVEMENTS : references
    ORDER_ITEMS ||--o| REVIEWS : reviewed_by

    USERS {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        enum role
        tinyint is_active
    }
    STORES {
        bigint id PK
        bigint seller_id FK,UK
        varchar name
        varchar slug UK
        varchar location
        tinyint is_open
    }
    CATEGORIES {
        bigint id PK
        varchar name UK
        varchar slug UK
        tinyint is_active
    }
    PRODUCTS {
        bigint id PK
        bigint store_id FK
        bigint category_id FK
        varchar sku
        varchar name
        decimal price
        int stock
        tinyint is_available
    }
    ORDERS {
        bigint id PK
        varchar order_code UK
        bigint buyer_id FK
        bigint store_id FK
        enum order_status
        decimal subtotal
        decimal total
        enum payment_method
        char pickup_code
    }
    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        varchar product_name
        decimal unit_price
        smallint quantity
        decimal line_total
    }
    PAYMENTS {
        bigint id PK
        bigint order_id FK,UK
        enum payment_method
        enum payment_status
        decimal amount
        varchar payment_reference
        datetime paid_at
    }
    STOCK_MOVEMENTS {
        bigint id PK
        bigint store_id FK
        bigint product_id FK
        bigint actor_user_id FK
        bigint order_id FK
        enum movement_type
        int quantity_change
    }
    REVIEWS {
        bigint id PK
        bigint order_item_id FK,UK
        bigint order_id FK
        bigint product_id FK
        bigint buyer_id FK
        tinyint rating
        varchar comment
    }
```

## Relationship notes

- `stores.seller_id` is `UNIQUE`, so one seller can own at most one tenant store.
- A store can list many products and receive many orders.
- Each order belongs to exactly one buyer and one store.
- An order can contain multiple `order_items`, but the application and database trigger ensure every item belongs to the same store as its order.
- Each order has one payment record.
- `order_items.product_name` and `unit_price` are snapshots, preserving order history if the seller later edits the product.
- `reviews.order_item_id` is unique, so one purchased item can receive at most one review.
- `stock_movements.quantity_change` uses negative numbers for stock outflow and positive numbers for stock inflow.
