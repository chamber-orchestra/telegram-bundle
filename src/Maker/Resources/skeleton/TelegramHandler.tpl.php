<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

<?= $use_statements ?>

<?= $filter_attribute ?>

final class <?= $class_name ?> extends AbstractActionHandler
{
    public function __invoke(AbstractData $dto): void
    {
        // TODO: implement handler logic
    }
}
