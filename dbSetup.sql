
Create Database recipe_db;

use recipe_db;

CREATE TABLE recipes (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(255) NOT NULL,
  ingredients   TEXT    NOT NULL,
  instructions  TEXT    NOT NULL,
  image_path    VARCHAR(500) DEFAULT NULL,
  created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);