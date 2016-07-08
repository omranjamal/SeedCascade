<?php
namespace Hedronium\SeedCascade\Traits;

trait DataPersistence
{
    /**
     * Creates Models, assigns data as
     * properties and saves them.
     *
     * @param array $data    An key value hash of the data
     * @return null
     */
    protected function insertModel(array $data)
    {
        $model = new $this->model;

        foreach ($data as $property => $value) {
            $model->{$property} = $value;
        }

        $model->save();
    }

    /**
     * Inserts data directly into the database
     *
     * @param array $data    An key value hash of the data
     * @return null
     */
    protected function insertRow(array $data)
    {
        DB::table($this->table)->insert($data);
    }

    /**
     * Decides a method for Data Insertion
     *
     * @param int $i    The current iteration count.
     * @param array $data    An key value hash of the data
     * @return null
     */
    protected function insertData($i, array $data)
    {
        if ($this->table != null) {
            // Insert with the Table Mechanism
            $this->insertRow($data);
        } else {
            // Insert with Model
            $this->insertModel($data);
        }
    }
}
