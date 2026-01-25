## PHP:
- Use strict mode.
- ALWAYS use full variable names.
- ALWAYS import namespaces.
- Project has 2 DI containers: use PHP-DI, don't use legacy custom. Use project/core/di-definitions.php for definitions.
- Don't add empty autowire into di. Autowiring is turned on by default.
- NEVER add unnesessary duplicate type casting. 
- Use typed constants (e.g., `public const int MY_CONSTANT = 1;`).
- Place constants and variables at the beginning of the class, before any methods.