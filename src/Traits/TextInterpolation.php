<?php
namespace Hedronium\SeedCascade\Traits;

use Hedronium\SeedCascade\SelfResolver;
use Hedronium\SeedCascade\Inheriter;

trait TextInterpolation
{
    /**
	 * Handles simple text imagesetinterpolation
	 *
	 * @param mixed $prop the value of the property
	 * @param string $property the property name.
	 * @param int $i the current iteration count.
	 * @param SelfResolver $self the MagicResolver instance for self.
	 * @param Inheriter $inherit the MagicResolver instance for inheritance.
	 *
	 * @return string  the string with all the data interpolated.
	 */
	protected function interpolateText($prop, $property, $i, SelfResolver $self, Inheriter $inherit)
	{
		// Replace {self.*} and {inherit.*}
		$prop = preg_replace_callback(
			'/\{(self|inherit)\.([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+)\}/',
			function ($matches) use (&$self, &$inherit) {
				list($expression, $source, $property) = $matches;

				if ($source === 'self') {
					return $self->get($property);
				} elseif ($source === 'inherit') {
					return $inherit->get($property);
				}
			},
			$prop
		);

		// Replace {i} with the current iteration number
		$prop = preg_replace('/\{i\}/', $i, $prop);

		// Replace {inherit} with the inherit value of higher blocks.
		$prop = preg_replace(
			'/\{inherit\}/',
			$inherit(),
			$prop
		);

		// replace excaped curly braces
		$prop = preg_replace_callback(
			'/\\\(\{|\})/',
			function ($matches) {
				return $matches[1];
			},
			$prop
		);

		return $prop;
	}
}
