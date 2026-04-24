<?php
$models = glob('app/Models/*.php');
foreach ($models as $model) {
    $content = file_get_contents($model);
    
    // Add implements
    if (!str_contains($content, 'implements \OwenIt\Auditing\Contracts\Auditable')) {
        $content = preg_replace('/class (\w+)( extends Model| extends Authenticatable)?\n\{/', "class $1$2 implements \OwenIt\Auditing\Contracts\Auditable\n{", $content);
    }
    
    // Add Trait
    if (!str_contains($content, 'use \OwenIt\Auditing\Auditable;')) {
        $content = preg_replace('/\{\n    use /', "{\n    use \OwenIt\Auditing\Auditable, ", $content);
    }
    
    file_put_contents($model, $content);
}
echo "Models made auditable successfully.\n";
