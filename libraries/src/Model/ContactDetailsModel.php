<?php

/**
 * @package    Kumwe CMS
 *
 * @created    21st December 2022
 * @author     John Adriaans
 * @git        Kumwe CMS <https://git.vdm.dev/Kumwe/cms>
 * @license    GNU General Public License version 2; see LICENSE.txt
 */

namespace Kumwe\CMS\Model;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Model\DatabaseModelInterface;
use Joomla\Model\DatabaseModelTrait;
use Kumwe\CMS\Date\Date;
use stdClass;

/**
 * Model class
 */
class ContactDetailsModel implements DatabaseModelInterface
{
	use DatabaseModelTrait;

	/**
	 * Instantiate the model.
	 *
	 * @param   DatabaseDriver  $db  The database adapter.
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->setDb($db);
	}

	/**
	 * Add an item
	 *
	 * @param   int  $id
	 * @param   int  $typeId
	 * @param   string  $value
	 *
	 * @return  int
	 * @throws \Exception
	 */
	public function setItem(
		int    $id,
		int    $typeId,
		string $value
	): int {
		$db = $this->getDb();

		$data = [
			'id'            => (int) $id,
			'typeId'            => (int) $typeId,
			'value'        => (string) $value,
		];

		// if we have ID update
		if ($id > 0) {
			$data['id'] = (int) $id;

			// change to object
			$data = (object) $data;

			try {
				$db->updateObject('#__contactDetails', $data, 'id');
			} catch (\RuntimeException $exception) {
				throw new \RuntimeException($exception->getMessage(), 404);
			}

			return $id;
		} else {
			// change to object
			$data = (object) $data;

			try {
				$db->insertObject('#__contactDetails', $data);
			} catch (\RuntimeException $exception) {
				throw new \RuntimeException($exception->getMessage(), 404);
			}

			return $db->insertid();
		}
	}

	/**
	 * Get an item
	 *
	 * @param   int|null  $id
	 *
	 * @return \stdClass
	 * @throws \Exception
	 */
	public function getItem(?int $id): \stdClass
	{
		$db = $this->getDb();

		$default = new stdClass();
		$default->today_date = (new Date())->toSql();
		$default->post_key   = "?task=create";

		// return empty object if id not correct
		if (!is_numeric($id)) {
			return $default;
		}

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__contactDetails'))
			->where($db->quoteName('id') . ' = :id')
			->bind(':id', $id, ParameterType::INTEGER)
			->setLimit(1);

		try {
			$result = $db->setQuery($query)->loadObject();
		} catch (\RuntimeException $e) {
			// we ignore this and just return an empty object
		}

		if (isset($result) && $result instanceof \stdClass) {
			$result->post_key   = "?id=$id&task=edit";
			$result->today_date = $default->today_date;

			return $result;
		}

		return $default;
	}

	/**
	 * Get items
	 *
	 * @return \array
	 * @throws \Exception
	 */
	public function getItems(): array
	{
		$db = $this->getDb();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__contactDetails'));

		return $db->setQuery($query)->loadObjectList('id');
	}

	/**
	 * @param   string  $name
	 *
	 * @return string
	 */
	public function setLayout(string $name): string
	{
		return $name . '.twig';
	}

	/**
	 * @param   int  $id
	 *
	 * @return bool
	 */
	public function delete(int $id): bool
	{
		$db = $this->getDb();

		// Purge the session
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__contactDetails'))
			->where($db->quoteName('id') . ' = :id')
			->bind(':id', $id, ParameterType::INTEGER);
		try {
			$db->setQuery($query)->execute();
		} catch (\RuntimeException $e) {
			// delete failed
			return false;
		}

		return true;
	}
}
