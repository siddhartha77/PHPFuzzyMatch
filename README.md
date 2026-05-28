# PHPFuzzyMatch
Fuzzy match elements of an array in PHP. I tried to get this as close as possible to the great filename searching in Google Drive. It accounts for typos, but factors in substrings.

# Examples
- 'Ringo' will weigh 'Mr. Ringo' higher than 'Bingo'.
- 'John Paul George' will weigh 'Ringo Paul George' higher than 'John Ringo'.
- 'John Paul George' will weigh 'Ringo George Paul' higher than 'John Paul'.

# Use
`FuzzyMatch::Match($haystack_array, $needle_string, $limit);`
