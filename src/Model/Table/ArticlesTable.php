<?php
// src/Model/Table/ArticlesTable.php
namespace App\Model\Table;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Validation\Validator;
class ArticlesTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');
        $this->belongsToMany('Tags'); 
    }

    public function beforeSave($event, $entity, $options)
{
    if ($entity->isNew() && !$entity->slug) {
        $sluggedTitle = Text::slug($entity->title);
        // trim slug to maximum length defined in schema
        $entity->slug = substr($sluggedTitle, 0, 191);
    }
}
public function validationDefault(Validator $validator)
{
    $validator
        ->allowEmptyString('title', 'Title cannot be empty', false)
        ->minLength('title', 5)
        ->maxLength('title', 255);

    $validator
        ->allowEmptyString('body', 'Body cannot be empty', false)
        ->minLength('body', 5);
    $validator
        ->allowEmptyString('Tags', 'Body cannot be empty', false)
        ->minLength('body', 5);

    return $validator;
}
public function findTagged(Query $query, array $options)
        {
            $columns = [
                'Articles.id', 'Articles.user_id', 'Articles.title',
                'Articles.body', 'Articles.published', 'Articles.created',
                'Articles.slug',
            ];

            $query = $query
                ->select($columns)
                ->distinct($columns);

            if (empty($options['tags'])) {
                // If there are no tags provided, find articles that have no tags.
                $query->leftJoinWith('Tags')
                    ->where(['Tags.title IS' => null]);
            } else {
                // Find articles that have one or more of the provided tags.
                $query->innerJoinWith('Tags')
                    ->where(['Tags.title IN' => $options['tags']]);
            }

            return $query->group(['Articles.id']);
        }
}