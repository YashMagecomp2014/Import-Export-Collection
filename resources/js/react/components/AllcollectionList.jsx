import React, { useEffect, useState } from "react"
import { GlobalAPIcall } from "../config/ApiUtils"
import CollectionPage from "./CollectionPage"
import Dropdown from "./Dropdown"

function AllcollectionList() {
  const [collections, setUsers] = useState([])

  const fetchData = async () => {
    var res = await GlobalAPIcall('GET', '/import');
          setUsers(res)
    }

  useEffect(() => {
    fetchData()
  }, [])

  return (
    <table className="table">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">Collections</th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody>
        {collections.map(collection => (
          <tr>
            <td>{collection.id}</td>
            <td>{collection.file}</td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}

export default AllcollectionList;