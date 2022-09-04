import {ReleaseData} from './release-data';

export interface ParserData {
  files: ParserData[],
  md5: string,
  name: string,
  type: string,
  releases: ReleaseData[],
}
