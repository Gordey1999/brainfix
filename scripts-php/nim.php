<?php


$result = [];
for ($i = 1; $i <= 3; $i++)
{
	for ($j = 1; $j <= 5; $j++)
	{
		for ($k = 1; $k <= 7; $k++)
		{
			if (($i ^ $j ^ $k ^ 1) === 0)
			{
				$result[] = [1, $i, $j, $k];
			}
		}
	}
}

echo '<pre>';
print_r($result);
echo '</pre>';