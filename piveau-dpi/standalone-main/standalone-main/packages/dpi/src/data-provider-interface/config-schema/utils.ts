export type DeepPartial<Thing> = Thing extends Function
  ? Thing
  : Thing extends Array<infer InferrerArrayMember>
    ? DeepPartialArray<InferrerArrayMember>
    : Thing extends object
      ? DeepPartialObject<Thing>
      : Thing | undefined

interface DeepPartialArray<Thing> extends Array<DeepPartial<Thing>> {}

type DeepPartialObject<Thing> = {
  [Key in keyof Thing]?: DeepPartial<Thing[Key]>;
}
