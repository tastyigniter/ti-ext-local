<?php
/**
 * Create locationables table
 */

class Migration_create_locationables_table extends TI_Migration
{
    public function up()
    {
        $fields = [
            'location_id INT(11) NOT NULL',
            'locationable_id INT(11) NOT NULL',
            'locationable_type VARCHAR(255) NOT NULL',
            'UNIQUE (location_id, locationable_id, locationable_type)',
        ];

        $this->dbforge->add_field($fields);
        $this->dbforge->create_table('locationables');

        $fields = [
            'menu_id INT(11) NOT NULL',
            'category_id INT(11) NOT NULL',
            'UNIQUE (menu_id, category_id)',
        ];

        $this->dbforge->add_field($fields);
        $this->dbforge->create_table('menu_categories');

        $query = $this->db->get('menus');
        foreach ($query->result() as $row) {
            $this->db->set('menu_id', $row->menu_id);
            $this->db->set('category_id', $row->menu_category_id);
            $this->db->insert('menu_categories');
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('locationables');
        $this->dbforge->drop_table('menu_categories');
    }
}
