<?php

namespace Micro;

/**
 * Class to display associative arrays (such as database records) using closure callbacks for each column.
 */
class Table
{
	// Array of data rows
	public $rows;

	// List of all table columns
	public $columns;

	/**
	 * Create the table object using these rows
	 *
	 * @param array $rows to use
	 */
	public function __construct(array $rows)
	{
		$this->rows = $rows;
	}

	/**
	 * Add a new field to the validation object
	 *
	 * @param string $field name
	 */
	public function column($column, $name, $function = NULL, $sortable = TRUE)
	{
		$this->columns[$column] = array($name, $function, $sortable);
		return $this;
	}

	public function getColumns()
	{
		return array_keys($this->columns);
	}

	public function __invoke($column = NULL, $sort = 'desc', $params = array())
	{
		$html = "\n\t<thead>\n\t\t<tr>";

		foreach($this->columns as $key => $data)
		{
			$html .= "\n\t\t\t<th>";

			// If we allow sorting by this column
			if($data[2])
			{
				$direction = $sort == 'desc' ? 'asc' : 'desc';

				// Build URL parameters taking existing parameters into account
				$url = site_url(URL_PATH, array('column' => $key, 'sort' => $direction) + $params);

				$html .= '<a href="' . $url . '">' . $data[0] . '</a>';
			}
			else
			{
				$html .= $data['0'];
			}

			$html .= "</th>";
		}

		$html .= "\n\t\t</tr>\n\t</thead>\n\t<tbody>";

		$odd = 0;
		foreach($this->rows as $row)
		{
			$odd = 1 - $odd;

			$html .= "\n\t\t<tr class=\"". ($odd ? 'odd' : 'even') . '">';
			foreach($this->columns as $column => $data)
			{
				$html .= "\n\t\t\t<td>" . ($data[1] ? $data[1]($row) : $row[$column]) . "</td>";
			}

			$html .= "\n\t\t</tr>";
		}

		$html .= "\n\t</tbody>\n";

		return '<table>' . $html . "</table>\n";
	}
}