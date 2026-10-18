export interface ProdDescriptionDto {
  description: string;
  /** Whether the description is formatted HTML rather than plain text. */
  htmlDescription: boolean;
  /** Whether the instructions are formatted HTML rather than plain text. */
  htmlInstructions: boolean;
  instructions: string;
}
