# Limonade bootstrap

## Installation

	git clone git://github.com/aimerickdesdoit/php-limonade-bootstrap.git
	cd php-limonade-bootstrap
	curl -sS https://getcomposer.org/installer | php
	php composer.phar install

## Configuration

config/settings.inc.php

    <?php
    
    define('BASE_URI', '/php-limonade-bootstrap/public/');
    
    define('DB_HOST',     '127.0.0.1:8889');
    define('DB_NAME',     'php-limonade-bootstrap');
    define('DB_USER',     'root');
    define('DB_PASSWORD', 'root');

## Développement

### Migration

Création d'un fichier de migration

    rake db:create_migration NAME=create_records

Édition de la migration

    class CreateRecords < ActiveRecord::Migration
      def self.up
        create_table :records do |t|
          t.string :label
          t.timestamps
        end
      end
    
      def self.down
        drop_table :records
      end
    end

Exécution de la migration

    rake db:migrate

### Model

Création du modèle

    <?php
    
    class Record extends Model {
    
      protected static $_table_name = 'records';
    
      public function validate() {
        if (!$this->label) {
          $this->_errors['label'] = 'champ obligatoire';
        }
    
        return count($this->_errors) == 0;
      }
    
    }

Utilisation du modèle

    $record = new Record(array('label' => 'lorem ipsum'));
    $record->save();