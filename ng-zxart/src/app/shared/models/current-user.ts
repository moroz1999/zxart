export interface CurrentUser {
  id: number | null;
  userName: string;
  authorId: number | null;
  /** Element that site-wide privileges are held on; see `RootPrivilegeService`. */
  publicRootId: number;
}
